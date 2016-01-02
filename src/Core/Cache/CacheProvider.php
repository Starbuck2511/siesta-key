<?php

namespace Core\Cache;

class CacheProvider {

    private $cache;

    public function __construct(CacheInterface $cache) {
        $this->cache= $cache;
    }

    public function getCache() {
        return $this->cache;
    }


}