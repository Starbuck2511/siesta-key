<?php

namespace AppBundle\Controller;

use Core\Cache\CacheProvider;
use Core\App\Group;
use Symfony\Component\HttpFoundation\Response;


class GroupController
{

    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    private $cache;

    private $cacheEnabled;

    private $group;

    public function __construct(CacheProvider $cacheProvider, Group $group, CacheController $cacheController)
    {
        /**
         * @var \Core\Cache\CacheInterface
         */
        $this->cache = $cacheProvider->getCache();
        $this->group = $group;
        $this->cacheEnabled = $cacheController->isCacheEnabled();
    }


    public function listGroupsAction()
    {
        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('groups:all');
        }

        if (!$data) {
            $data = $this->group->listGroups();

            if ($this->cacheEnabled) {
                $this->cache->setItem('groups:all', $data);
            }
        }
        $this->sendJsonResponse($data);
    }

    public function createGroupAction()
    {
        $data = $this->group->createGroup();
        $this->cache->deleteItem('groups:all');
        $this->sendJsonResponse($data);
    }

    private function sendJsonResponse($data)
    {
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        $response->send();
    }


}
