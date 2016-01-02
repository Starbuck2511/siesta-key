<?php
namespace Core\Cache\Predis;

use Core\Cache\CacheInterface;
use \Predis\Client as Predis;

class PredisAdapter implements CacheInterface {

    private $predis;

    public function __construct(Predis $predis) {
        $this->predis = $predis;
    }

    public function setItem($key, $value)
    {
        $this->predis->set($key, $value);
    }

    public function getItem($key)
    {
        return $this->predis->get($key);
    }

    public function setTtl($key, $time)
    {
        // TODO: Implement setTtl() method.
    }

    public function getItems(array $keys = array())
    {
        // TODO: Implement getItems() method.
    }

    public function hasItem($key)
    {
        // TODO: Implement hasItem() method.
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function deleteItem($key)
    {
        $this->predis->del($key);
    }

    public function deleteItems(array $keys)
    {
        // TODO: Implement deleteItems() method.
    }

}