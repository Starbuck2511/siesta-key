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

    /**
     * @return array|\Core\Documents\Group[]
     */
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
            throw new NotFoundHttpException('No group found');
        }

        $data = $this->data->prepare($group, array('groups' => array('group1')));

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
            $data = $this->data->prepare($group, array('groups' => array('group1')));
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

        $group->addUser($user);
        $this->dm->persist($group);

        if (!empty($errors)) {
            $data = $this->errorHandler->handle($errors);
        } else {
            $this->dm->flush();
            $data = $this->data->prepare($group, array('groups' => array('group1')));
        }

        return $data;
    }

    public function deleteGroupUser($id)
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
            throw new NotFoundHttpException('No group found');
        }

        // user is not in group yet, so add him now
        $group->removeUser($user);
        $this->dm->persist($group);
        $this->dm->flush();

        $data = $this->data->prepare($group, array('groups' => array('group1')));

        return $data;
    }
}