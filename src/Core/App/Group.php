<?php
namespace Core\App;

use Core\Data\DataHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Core\Documents\Group as GroupDocument;
use Core\Data\DataHandler as ResponseData;
use Core\Error\ErrorHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);

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

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);

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

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
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

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $param = $this->getRequestParamAsArray();

        $name = trim($param->get('name'));
        $description = $param->get('description');


        $group = new GroupDocument();
        $group->setName($name);
        $group->setDescription($description);
        $group->addUser($user);
        $this->dm->persist($group);
        $user->addAdminGroup($group->getId());
        $user->addMemberGroup($group->getId());
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

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
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

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
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
        $exists = false;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $param = $this->getRequestParamAsArray();

        $startDate = trim($param->get('startDate'));


        //$date = '2016-01-03 10:00:00';
        $startDate = new \DateTime($startDate);
        $date = $startDate->format('Y-m-d H:i:s');
        $scheduleId = md5($date);

        // check if this schedule already exists
        $schedules = $group->getSchedules();
        foreach ($schedules as $schedule) {
            if ($schedule['id'] === $scheduleId) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $weekday = date('w', strtotime($date));
            $time = date('H:i:s', strtotime($date));
            $group->addSchedule([
                'id' => $scheduleId,
                'startDate' => $date,
                'weekday' => $weekday,
                'time' => $time,
                'type' => 'WEEKLY'
            ]);
            $this->dm->persist($group);
            $this->dm->flush();
            $data = $this->data->prepare(true);
        } else {
            throw new Exception\ConstraintDefinitionException('This schedule already exists in this group');
        }

        return $data;
    }

    public function deleteGroupSchedule($id, $scheduleId)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $group->removeSchedule($scheduleId);
        $this->dm->persist($group);
        $this->dm->flush();
        $data = $this->data->prepare(true);

        return $data;
    }

    public function createGroupAppointmentAccept($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $group->removeAppointmentDecline($user->getId());
        $group->addAppointmentAccept($user->getId());
        $this->dm->persist($group);
        $this->dm->flush();
        $data = $this->data->prepare(true);


        return $data;
    }

    public function createGroupAppointmentDecline($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        }

        $group->removeAppointmentAccept($user->getId());
        $group->addAppointmentDecline($user->getId());
        $this->dm->persist($group);
        $this->dm->flush();
        $data = $this->data->prepare(true);


        return $data;
    }

    public function listGroupSchedules($id)
    {
        $data = null;

        /**
         * @var \Core\Documents\User
         */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new TokenNotFoundException();
        }

        $group = $this->dm->getRepository('Documents:Group')->findGroupById($id);
        if (!$group) {
            throw new NotFoundHttpException('No group found for id ' . $id);
        } else {
            $schedules = $group->getSchedules();
        }

        $data = $this->data->prepare($schedules);

        return $data;
    }

    private function getRequestParamAsArray(){

        $request = $this->requestStack->getCurrentRequest();
        $content = $request->getContent();
        $content = json_decode($content, true);

        return new ArrayCollection($content);

    }


}