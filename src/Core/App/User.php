<?php
namespace Core\App;

use Core\Data\DataHandler;
use Doctrine\ODM\MongoDB\DocumentManager;
use Core\Data\DataHandler as ResponseData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class User
{
    protected $data;

    protected $dm;

    protected $requestStack;

    protected $tokenStorage;

    /**
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param DataHandler $data
     *
     */
    public function __construct(DocumentManager $dm,  RequestStack $requestStack, TokenStorage $tokenStorage, ResponseData $data) {
        $this->dm = $dm;
        $this->data = $data;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function listUsers()
    {
        $users = $this->dm->getRepository('Documents:User')->findAll();

        if (!$users) {
            throw new NotFoundHttpException('No users found');
        }

        $data = $this->data->prepare($users, array('groups' => array('group1')));

        return $data;

    }

    public function createUser()
    {
        // user is created by FOSUserBundle
    }

    public function listUser($id)
    {
        echo 'list user with $id = ' . $id;
    }
}