<?php
/**
 * Redis Cache Adapter for SimpleCache
 */
namespace Wind\Cache;

use Wind\Redis\Redis;
use function Amp\call;
use function Amp\Promise\all;

class RedisCache extends BaseCache implements \Psr\SimpleCache\CacheInterface
{

    private $redis;

    public function __construct(Redis $redis)
    {
        parent::init();
        $this->redis = $redis;
    }

    public function get($key, $defaultValue=null)
    {
        return call(function() use ($key, $defaultValue) {
            $data = yield $this->redis->get($this->prefix.$key);
            return $data !== null ? unserialize($data) : $defaultValue;
        });
    }

    public function set($key, $value, $ttl=null)
    {
        $value = serialize($value);
        return $this->redis->set($this->prefix.$key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->redis->del($this->prefix.$key);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function getMultiple($keys, $default = null)
    {
        return call(function() use ($keys, $default) {
            $arr = yield $this->redis->mGet($this->keys($keys));

            $data = [];
            foreach ($keys as $i => $k) {
                $data[$k] = isset($arr[$i]) ? unserialize($arr[$i]) : $default;
            }
            return $data;
        });
    }

    public function setMultiple($values, $ttl = null)
    {
        $mValues = [];

        foreach ($values as $k => $v) {
            $mValues[$this->prefix.$k] = serialize($v);
        }

        if ($ttl) {
            $ps = [];
            foreach ($mValues as $k => $v) {
                $ps[] = $this->redis->set($k, $v, $ttl);
            }
            return all($ps);
        } else {
            return $this->redis->mset($mValues);
        }
    }

    public function deleteMultiple($keys)
    {
        return call_user_func_array([$this->redis, 'del'], $this->keys($keys));
    }

    public function has($key)
    {
        return $this->redis->exists($this->prefix.$key);
    }
}
