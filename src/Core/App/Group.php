<?php
namespace Core\App;

use Core\Data\DataHandler;
use Doctrine\ODM\MongoDB\DocumentManager;
use Core\Documents\Group as GroupDocument;
use Core\Data\DataHandler as ResponseData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class Group
{
    protected $data;

    protected $dm;

    protected $requestStack;

    protected $tokenStorage;

    protected $validator;

    protected $errors;

    /**
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param DataHandler $data
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        ResponseData $data,
        ValidatorInterface $validator
    ) {
        $this->dm = $dm;
        $this->data = $data;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
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

        $name = trim($request->request->get('name'));
        $description = $request->request->get('description');

        $group = new GroupDocument();
        $group->setName($name);
        $group->setDescription($description);
        $group->addUser($user);
        $this->dm->persist($group);
        $user->addAdminGroup($group->getId());
        $this->dm->persist($user);

        $errors = $this->validator->validate($group);

        if ((!$this->dm->getRepository('Documents:Group')->isUnique('name', $name))) {
            $this->errors[] = 'Not a unique value for field name';
        }

        if (count($errors) > 0 || !empty($this->errors)) {
            dump($errors);
            dump($this->errors);
            exit('debug exit ...');
        } else {
            $this->dm->flush();
            $data = $this->data->prepare($group, array('groups' => array('group1')));
        }
        return $data;
    }

    public function createGroupUser($id)
    {
        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new NotFoundHttpException('No user found');
        }

        $group = $this->dm->getRepository('Documents:Group')->findGropuById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found');
        }

        $group->addUser($user);
        $this->dm->persist($group);
        $this->dm->flush();


        $data = $this->data->prepare($group, array('groups' => array('group1')));

        return $data;
    }

    public function deleteGroupUser($id)
    {
        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new NotFoundHttpException('No user found');
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