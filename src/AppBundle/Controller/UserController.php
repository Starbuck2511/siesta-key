<?php

namespace AppBundle\Controller;

use Core\Cache\CacheProvider;
use Core\App\User;
use Symfony\Component\HttpFoundation\Response;


class UserController
{
    private $cache;

    private $cacheEnabled;

    private $user;

    public function __construct(CacheProvider $cacheProvider, User $user, CacheController $cacheController)
    {
        /**
         * @var \Core\Cache\CacheInterface
         */
        $this->cache = $cacheProvider->getCache();
        $this->user = $user;
        $this->cacheEnabled = $cacheController->isCacheEnabled();
    }

    public function listUserAction()
    {
        exit('listUserAction');
    }


    public function listUsersAction()
    {

        $data = null;

        if ($this->cacheEnabled) {
            $data = $this->cache->getItem('users:all');
        }

        if (!$data) {
            $data = $this->user->listUsers();

            if ($this->cacheEnabled) {
                $this->cache->setItem('groups:all', $data);
            }
        }
        $this->sendJsonResponse($data);
    }

    public function createUserAction()
    {
        // user is created by FOSUserBundle

    }

    private function sendJsonResponse($data)
    {
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'application/json');
        $response->send();
    }


}
