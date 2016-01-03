<?php

namespace AppBundle\Controller;

class CacheController
{
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    private $cacheEnabled = null;

    public function isCacheEnabled()
    {
        if (null === $this->cacheEnabled) {
            $this->cacheEnabled = $this->container->getParameter('app.cache.result_cache');
        }

        return $this->cacheEnabled;
    }
}
