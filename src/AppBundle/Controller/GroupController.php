<?php

namespace AppBundle\Controller;

use Core\Cache\CacheProvider;
use Core\App\Group;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;


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
        return $this->prepareJsonResponse($data);
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
        return $this->prepareJsonResponse($data);
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
        return $this->prepareJsonResponse($data);
    }

    public function createGroupAction()
    {
        $data = $this->group->createGroup();

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('groups:all');
        }

        return $this->prepareJsonResponse($data);
    }

    public function deleteGroupAction($id)
    {
        $data = $this->group->deleteGroup($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('groups:all');
        }

        return $this->prepareJsonResponse($data);
    }

    public function createGroupUserAction($id)
    {
        $data = $this->group->createGroupUser($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function deleteGroupUserAction($id)
    {
        $data = $this->group->deleteGroupUser($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function createGroupScheduleAction($id)
    {
        $data = $this->group->createGroupSchedule($id);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function deleteGroupScheduleAction($id, $scheduleId)
    {
        $data = $this->group->deleteGroupSchedule($id, $scheduleId);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function createGroupScheduleAcceptAction($id, $scheduleId)
    {
        $data = $this->group->createGroupScheduleAccept($id, $scheduleId);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function createGroupScheduleDeclineAction($id, $scheduleId)
    {
        $data = $this->group->createGroupScheduleDecline($id, $scheduleId);

        if ($this->cacheEnabled) {
            $this->cache->deleteItem('group:'. $id);
        }

        return $this->prepareJsonResponse($data);
    }

    public function listGroupSchedulesAction($id)
    {
        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('group:' . $id);
        }

        if (!$data) {
            $data = $this->group->listGroupSchedules($id);

            if ($this->cacheEnabled) {
                $this->cache->setItem('group:' . $id, $data);
            }
        }
        return $this->prepareJsonResponse($data);
    }

    public function listGroupScheduleAction($id, $scheduleId)
    {
        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('group:' . $id);
        }

        if (!$data) {
            $data = $this->group->listGroupSchedule($id, $scheduleId);

            if ($this->cacheEnabled) {
                $this->cache->setItem('group:' . $id, $data);
            }
        }
        return $this->prepareJsonResponse($data);
    }

    private function prepareJsonResponse($data)
    {
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
