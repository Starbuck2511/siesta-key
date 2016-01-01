<?php

namespace Core\Repository\MongoDb;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function findUserById($id)
    {
        return $this->findOneBy(array('id' => $id));
    }
}