<?php

namespace Wind\Cache;

abstract class BaseCache
{

    protected $prefix;

    protected function init()
    {
        $this->prefix = config('cache.prefix', '');
    }

    protected function keys($keys)
    {
        return array_map(function($k) {
            return $this->prefix.$k;
        }, $keys);
    }

}
