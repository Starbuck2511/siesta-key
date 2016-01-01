<?php
namespace Core\App;

use \Predis\Client as Predis;

class User
{

    protected $redis;

    /**
     * @param Predis $predis
     */
    public function __construct(Predis $predis) {
        $this->redis = $predis;
    }

    public function listUsers()
    {
        echo 'list users ...';
        $users = $this->redis->hgetall('users');
        var_dump($users);

    }

    public function createUser()
    {

        echo 'create user ...';
        $userName = 'starbuck@gmx-topmail.de';
        $nextUserId = $this->redis->incr('next_user_id');
        echo 'user id = ' . $nextUserId;
        $this->redis->hmset('user:'.$nextUserId, 'user_name', $userName);
        $this->redis->hset('users', $nextUserId, $userName);

    }

    public function listUser($id)
    {
        echo 'list user with $id = ' . $id;
    }
}