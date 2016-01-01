<?php

namespace Core\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @MongoDB\Document(collection="users", repositoryClass="Core\Repository\MongoDb\UserRepository")
 * @MongoDBUnique(fields="email")
 * @MongoDBUnique(fields="apiKey")
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $apiKey;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = md5(uniqid(rand(), true));
    }

}