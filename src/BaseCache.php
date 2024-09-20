<?php

namespace Wind\Cache;

abstract class BaseCache
{

    // protected $prefix;

    protected function init()
    {
        // $this->prefix = config('cache.prefix', '');
    }

    // protected function keys($keys)
    // {
    //     return array_map(fn($k) => $this->prefix.$k, $keys);
    // }

    protected function normalizeTTL(null|int|\DateInterval $ttl=null)
    {
        if ($ttl instanceof \DateInterval) {
            $ref = new \DateTimeImmutable();
            $ttl = $ref->add($ttl)->getTimestamp() - $ref->getTimestamp();
        }

        if ($ttl !== null && $ttl <= 0) {
            $ttl = null;
        }

        return $ttl;
    }

}
