<?php
namespace Core\App;

use Core\Data\DataHandler;
use Doctrine\ODM\MongoDB\DocumentManager;
use Core\Documents\Group as GroupDocument;
use Core\Data\DataHandler as ResponseData;
use Core\Error\ErrorHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception;

class Group
{
    protected $data;

    protected $dm;

    protected $requestStack;

    protected $tokenStorage;

    protected $validator;

    protected $errorHandler;

    /**
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param DataHandler $data
     * @param ValidatorInterface $validator
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        ResponseData $data,
        ValidatorInterface $validator,
        ErrorHandler $errorHandler
    ) {
        $this->dm = $dm;
        $this->data = $data;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->errorHandler = $errorHandler;
    }

    public function listGroups()
    {
        $groups = $this->dm->getRepository('Documents:Group')->findAll();

        if (!$groups) {
            throw new NotFoundHttpException('No groups found');
        }

        $data = $this->data->prepare($groups, array('groups' => array('group1')));

        return $data;
    }

    public function listGroup($id)
    {
        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);

        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $data = $this->data->prepare($group, array('groups' => array('group1')));

        return $data;
    }

    public function deleteGroup($id)
    {

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);

        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $adminGroups = $user->getAdminGroups();

        if (!in_array($id, $adminGroups)) {
            throw new Exception\ConstraintDefinitionException('No admin rights for this group');
        } else {
            $this->dm->remove($group);
            $user->removeAdminGroup($id);
            $this->dm->persist($user);
            $this->dm->flush();
            $data = $this->data->prepare(true);
        }
        return $data;
    }


    public function listGroupUsers($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        } else {
            $users = $group->getUsers();
        }

        $data = $this->data->prepare($users, array('groups' => array('group1')));

        return $data;
    }

    public function createGroup()
    {
        $data = null;

        $request = $this->requestStack->getCurrentRequest();
        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $name = trim($request->request->get('name'));
        $description = $request->request->get('description');

        $group = new GroupDocument();
        $group->setName($name);
        $group->setDescription($description);
        $group->addUser($user);
        $this->dm->persist($group);
        $user->addAdminGroup($group->getId());
        $this->dm->persist($user);

        $violations = $this->validator->validate($group);

        if ((!$this->dm->getRepository('Documents:Group')->isUnique('name', $name))) {
            throw new Exception\ConstraintDefinitionException('Not a unique value for group name');
        }

        if (count($violations) > 0) {
            $data = $this->errorHandler->handle($violations);
        } else {
            $this->dm->flush();
            $data = $this->data->prepare(true);
        }

        return $data;
    }

    public function createGroupUser($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $exists = $group->getUsers()->contains($user);
        if (!$exists) {
            $group->addUser($user);
            $this->dm->persist($group);
            $this->dm->flush();
            $data = $this->data->prepare(true);
        } else {
            throw new Exception\ConstraintDefinitionException('User already exists in this group');
        }

        return $data;
    }

    public function deleteGroupUser($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $exists = $group->getUsers()->contains($user);
        if ($exists) {
            $group->removeUser($user);
            $this->dm->persist($group);
            $this->dm->flush();
            $data = $this->data->prepare(true);
        } else {
            throw new Exception\ConstraintDefinitionException('User not exists in this group');
        }

        return $data;
    }

    public function createGroupSchedule($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $exists = false;
        if (!$exists) {
            $group->addSchedule(['startDate' => '2016-01-08 17:30:00', 'range' => 'SINGLE']);
            $this->dm->persist($group);
            $this->dm->flush();
            $data = $this->data->prepare(true);
        } else {
            throw new Exception\ConstraintDefinitionException('This schedule already exists in this group');
        }

        return $data;
    }
}