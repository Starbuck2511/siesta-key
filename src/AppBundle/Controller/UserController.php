<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    public function listUsersAction()
    {

        $this->container->get('app.user')->listUsers();
        exit;
    }

    public function createUserAction()
    {

        $this->container->get('app.user')->createUser();
        exit;
    }

    public function listUserAction($id)
    {


        $this->container->get('app.user')->listUser($id);
        exit;
    }


}
