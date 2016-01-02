<?php

namespace Core\Cache;

interface CacheInterface
{
    public function setItem($key, $value);

    public function setTtl($key, $time);

    public function getItem($key);

    public function getItems(array $keys = array());

    public function hasItem($key);

    public function clear();

    public function deleteItem($key);

    public function deleteItems(array $keys);
}