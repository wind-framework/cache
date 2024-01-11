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

    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->memcache->get($key);
        return $data !== false ? unserialize($data) : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = 0): bool
    {
        $value = serialize($value);
        return $this->memcache->set($key, $value, $ttl) !== false;
    }

    public function delete(string $key): bool
    {
        return $this->memcache->delete($key) !== false;
    }

    public function clear(): bool
    {
        return $this->memcache->flush() !== false;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $data = [];
        foreach ($keys as $k) {
            $data[$k] = $this->get($k, $default);
        }
        return $data;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $k) {
            $this->delete($k);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->memcache->get($key) !== false;
    }

}
