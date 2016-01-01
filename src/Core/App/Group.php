<?php
namespace Core\App;

use Doctrine\ODM\MongoDB\DocumentManager;
use \Predis\Client as Predis;
use Core\Documents\Group as GroupDocument;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Group
{
    protected $redis;

    protected $dm;

    /**
     * @param DocumentManager $dm
     * @param Predis $predis
     */
    public function __construct(DocumentManager $dm, Predis $predis) {
        $this->dm = $dm;
        $this->redis = $predis;
    }

    public function listGroups()
    {
        echo 'list groups ...';

        $groups = $this->dm->getRepository('Documents:Group')->findAll();

        if (!$groups) {
            throw new NotFoundHttpException('No groups found');
        }

        foreach ($groups as $group) {
            echo $group->getName();
        }


    }

    public function createGroup()
    {

        echo 'create group ...';

        $group = new GroupDocument();
        $group->setName('A Foo Bar Group');
        $group->setDescription('This is a foo bar description');


        $this->dm->persist($group);
        $this->dm->flush();

        echo 'created group with id =  ' .  $group->getId();

    }
}