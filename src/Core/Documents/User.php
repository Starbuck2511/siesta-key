<?php

namespace Core\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @MongoDB\Document(collection="users", repositoryClass="Core\Repository\MongoDb\UserRepository")
 * @MongoDBUnique(fields="apiKey")
 */
class User extends BaseUser
{
    /**
     * @MongoDB\Id(strategy="auto")
     * @Groups({"group1"})
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Assert\NotBlank()
     * @fixme Check if not already exists in db
     */
    protected $apiKey;

    /**
     * @Groups({"group1"})
     */
    protected $username;

    /**
     * @Groups({"group1"})
     */
    protected $email;

    /**
     * groups of the app where the user is admin
     *
     * @MongoDB\Field(type="collection")
     */
    protected $adminGroups = [];

    /**
     * groups of the app where the user is a member
     *
     * @MongoDB\Field(type="collection")
     */
    protected $memberGroups = [];


    public function __construct()
    {
        parent::__construct();
        $this->apiKey = md5(uniqid(rand(), true));
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getAdminGroups()
    {
        return $this->adminGroups;
    }

    /**
     * @param $group
     */
    public function addAdminGroup($group)
    {
        $this->adminGroups[] = $group;
    }

    /**
     * @param $group
     */
    public function removeAdminGroup($group)
    {
        if (($key = array_search($group, $this->adminGroups)) !== false) {
            unset($this->adminGroups[$key]);
        }
    }


    /**
     * @return mixed
     */
    public function getMemberGroups()
    {
        return $this->memberGroups;
    }

    /**
     * @param $group
     */
    public function addMemberGroup($group)
    {
        $this->memberGroups[] = $group;
    }

    /**
     * @param $group
     */
    public function removeMemberGroup($group)
    {
        if (($key = array_search($group, $this->memberGroups)) !== false) {
            unset($this->memberGroups[$key]);
        }
    }


}