<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends Controller
{

    public function listGroupsAction()
    {

        $this->container->get('app.group')->listGroups();
        exit;
    }

    public function createGroupAction()
    {

        $this->container->get('app.group')->createGroup();
        exit;
    }



}
