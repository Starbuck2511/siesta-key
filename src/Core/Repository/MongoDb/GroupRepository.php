<?php

namespace Core\Repository\MongoDb;

use Doctrine\ODM\MongoDB\DocumentRepository;

class GroupRepository extends DocumentRepository
{
    public function findGropuById($id)
    {
        return $this->findOneBy(array('id' => $id));
    }
}