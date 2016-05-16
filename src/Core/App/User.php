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

    public function listUserGroups($id) {

        $groups = [];

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        // only show groups that belong to the currently authenticated user
        if($id === $user->getId()) {
            $groupIds = $user->getMemberGroups();

            if(!empty($groupIds)){

                $groups = $this->dm->getRepository('Documents:Group')->findGroupsById($groupIds);
            }
        }




        $data = $this->data->prepare($groups, array('groups' => array('group1')));
        return $data;



    }
}