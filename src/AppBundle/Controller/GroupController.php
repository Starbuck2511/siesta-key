<?php

namespace AppBundle\Controller;

use Core\Cache\CacheProvider;
use Core\App\Group;
use Symfony\Component\HttpFoundation\Response;


class GroupController
{

    private $cache;

    private $group;

    public function __construct(CacheProvider $cacheProvider, Group $group)
    {
        /**
         * @var \Core\Cache\CacheInterface
         */
        $this->cache = $cacheProvider->getCache();

        $this->group = $group;
    }


    public function listGroupsAction()
    {
        $data = $this->cache->getItem('groups:all');

        if(!$data){
            $data = $this->group->listGroups();
            $this->cache->setItem('groups:all', $data);
        } else {
            echo 'cache hit ';
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
