<?php

namespace Core\Documents;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @MongoDB\Document(collection="groups", repositoryClass="Core\Repository\MongoDb\GroupRepository")
 * @MongoDB\HasLifecycleCallbacks
 * @MongoDB\Indexes({
 *   @MongoDB\Index(keys={"name"="asc"})
 * })
 */
class Group
{
    /**
     * @MongoDB\Id(strategy="auto")
     * @Groups({"group1"})
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(unique=true, order="asc")
     * @Assert\NotBlank()
     * @Groups({"group1"})
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"group1"})
     */
    protected $description;


    /**
     * @MongoDB\ReferenceMany(targetDocument="User")
     * @Groups({"group1"})
     */
    protected $users;

    /**
     * @MongoDB\Field(type="collection")
     * @Groups({"group1"})
     */
    protected $schedules = [];


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $user
     */
    public function addUser($user)
    {

        $this->users[] = $user;
    }

    /**
     * @param $user
     */
    public function removeUser($user)
    {

        $this->users->removeElement($user);
    }

    /**
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function validate()
    {

    }

    /**
     * @return mixed
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * @param $schedule
     */
    public function addSchedule($schedule)
    {
        $this->schedules[] = $schedule;
    }

    /**
     * @param $schedule
     */
    public function removeSchedule($schedule)
    {
        if (($key = array_search($schedule, $this->schedules)) !== false) {
            unset($this->schedules[$key]);
        }
    }

}