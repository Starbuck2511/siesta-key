<?php
namespace Core\App;

use Core\Data\DataHandler;
use Doctrine\ODM\MongoDB\DocumentManager;
use Core\Documents\Group as GroupDocument;
use Core\Data\DataHandler as ResponseData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


class Group
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

    public function createGroup()
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->tokenStorage->getToken()->getUser();

        $name = $request->request->get('name');
        $description = $request->request->get('description');

        $group = new GroupDocument();
        $group->setName($name);
        $group->setDescription($description);
        $group->addUser(['id' => $user->getId(), 'email' => $user->getEmail(), 'username' => $user->getUsername()]);

        $this->dm->persist($group);
        $this->dm->flush();

        $data = $this->data->prepare($group, array('groups' => array('group1')));

        return $data;

    }
}