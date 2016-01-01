<?php
namespace Core\App;

use Doctrine\ODM\MongoDB\DocumentManager;
use \Predis\Client as Predis;

class User
{
    protected $redis;

    protected $dm;

    /**
     * @param DocumentManager $dm
     * @param Predis $predis
     */
    public function __construct(DocumentManager $dm, Predis $predis) {
        $this->dm = $predis;
        $this->redis = $predis;
    }

    public function listUsers()
    {
        echo 'list users ...';

    }

    public function createUser()
    {

        echo 'create user ...';

    }

    public function listUser($id)
    {
        echo 'list user with $id = ' . $id;
    }
}