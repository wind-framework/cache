<?php

namespace Wind\Cache;

use Wind\Redis\Redis;

/**
 * Cache base on Redis
 */
class RedisCache implements \Psr\SimpleCache\CacheInterface
{

    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key, mixed $default=null): mixed
    {
        $data = $this->redis->get($key);
        return $data !== null ? unserialize($data) : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl=0): bool
    {
        $value = serialize($value);
        return $this->redis->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function clear(): bool
    {
        // TODO: Implement clear() method.
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $arr = $this->redis->mGet($keys);
        $data = [];
        foreach ($keys as $i => $k) {
            $data[$k] = isset($arr[$i]) ? $arr[$i] : $default;
        }
        return $data;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        return $this->redis->mSet($values);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return call_user_func_array([$this->redis, 'del'], $keys);
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key);
    }
}
