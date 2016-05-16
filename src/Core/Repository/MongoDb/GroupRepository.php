<?php

namespace Core\Repository\MongoDb;

use Doctrine\ODM\MongoDB\DocumentRepository;

class GroupRepository extends DocumentRepository
{
    public function findGroupById($id)
    {
        return $this->findOneBy(array('id' => $id));
    }

    public function findGroupsById($ids)
    {


        $qb = $this->createQueryBuilder()
            ->select()
            ->field('id')
            ->in($ids);
        $query = $qb->getQuery();
        return $query->execute();


    }

    public function isUnique($field, $value)
    {
        $result = false;
        $qb = $this->createQueryBuilder()
            ->select($field)
            ->field($field)
            ->equals($value)
            ->count();

        $query = $qb->getQuery();
        $count = $query->execute();

        if (0 == $count) {
            $result = true;
        }

        return $result;
    }
}