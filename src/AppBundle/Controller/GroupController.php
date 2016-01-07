<?php

namespace AppBundle\Controller;

use Core\Cache\CacheProvider;
use Core\App\Group;
use Symfony\Component\HttpFoundation\Response;


class GroupController
{
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

    public function listGroupAction($id)
    {
        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('group:' . $id);
        }

        if (!$data) {
            $data = $this->group->listGroup($id);

            if ($this->cacheEnabled) {
                $this->cache->setItem('group:' . $id, $data);
            }
        }
        $this->sendJsonResponse($data);
    }

    public function listGroupUsersAction($id)
    {
        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('group:' . $id);
        }

        if (!$data) {
            $data = $this->group->listGroupUsers($id);

            if ($this->cacheEnabled) {
                $this->cache->setItem('group:' . $id, $data);
            }
        }
        $this->sendJsonResponse($data);
    }

    public function createGroupAction()
    {
        $data = $this->group->createGroup();

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('groups:all');
        }

        $this->sendJsonResponse($data);
    }

    public function deleteGroupAction($id)
    {
        $data = $this->group->deleteGroup($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('groups:all');
        }

        $this->sendJsonResponse($data);
    }

    public function createGroupUserAction($id)
    {
        $data = $this->group->createGroupUser($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        $this->sendJsonResponse($data);
    }

    public function deleteGroupUserAction($id)
    {
        $data = $this->group->deleteGroupUser($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        $this->sendJsonResponse($data);
    }

    public function createGroupScheduleAction($id)
    {
        $data = $this->group->createGroupSchedule($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        $this->sendJsonResponse($data);
    }

    public function createGroupAppointmentAcceptAction($id)
    {
        $data = $this->group->createGroupAppointmentAccept($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        $this->sendJsonResponse($data);
    }

    public function createGroupAppointmentDeclineAction($id)
    {
        $data = $this->group->createGroupAppointmentDecline($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

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
