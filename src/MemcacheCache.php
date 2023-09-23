<?php

namespace Wind\Cache;

use Psr\SimpleCache\CacheInterface;
use Wind\Memcache\Memcache;

/**
 * Cache base on Memcache
 */
class MemcacheCache implements CacheInterface
{

    public function __construct(private Memcache $memcache)
    {
    }

    public function get($key, $default = null)
    {
        $data = $this->memcache->get($key);
        return $data !== false ? unserialize($data) : $default;
    }

    public function set($key, $value, $ttl = 0)
    {
        $value = serialize($value);
        return $this->memcache->set($key, $value, $ttl) !== false;
    }

    public function delete($key)
    {
        return $this->memcache->delete($key) !== false;
    }

    public function clear()
    {
        return $this->memcache->flush() !== false;
    }

    public function getMultiple($keys, $default = null)
    {
        $data = [];
        foreach ($keys as $k) {
            $data[$k] = $this->get($k, $default);
        }
        return $data;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $k) {
            $this->delete($k);
        }
        return true;
    }

    public function has($key)
    {
        return $this->memcache->get($key) !== false;
    }

}
