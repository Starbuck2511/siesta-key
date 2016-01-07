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

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"group1"})
     */
    protected $currentAppointment;

   /**
    * @MongoDB\Field(type="collection")
    */
    protected $appointmentAccepts = [];

    /**
     * @MongoDB\Field(type="collection")
     */
    protected $appointmentDeclines = [];


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
     * @todo we should add unit tests for this
     */
    public function setCurrentAppointment()
    {
        $dayNames = array(
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
        );
        $dates = [];
        $currentAppointment = null;
        $time = Date('H:i:s');
        $now = new \DateTime();
        $today = Date('w');
        $schedules = $this->getSchedules();
        if (!empty($schedules)) {
            foreach ($schedules as $schedule) {
                if (strtotime($schedule['startDate']) > time()) {
                    // date in future, just add to array
                    $dates[] = $schedule['startDate'];
                } elseif (strtotime($schedule['startDate']) < time()) {
                    // date is in the past
                    switch ($schedule['type']) {
                        case 'SINGLE':
                            continue;
                            break;
                        case 'WEEKLY':
                            // check if weekday is today
                            if ($today == $schedule['weekday']) {
                                // check if time has already passed
                                if ($time < $schedule['time']) {
                                    $dates[] = $now->format('Y-m-d') . ' ' . $schedule['time'];
                                } else {
                                    // time has passed for today, so calculate date for next week
                                    $nextWeek = $now->modify('+1 week');
                                    $dates[] = $nextWeek->format('Y-m-d') . ' ' . $schedule['time'];
                                }
                            } else {
                                // the weekday is not today, so get next date for next weekday
                                $nextWeekday = date('Y-m-d', strtotime('next ' . $dayNames[$schedule['weekday']]));
                                $dates[] = $nextWeekday . ' ' .$schedule['time'];
                            }
                            continue;
                            break;
                        case 'MONTHLY':
                            continue;
                            break;
                        default:
                            break;
                    }
                } elseif (strtotime($schedule['startDate']) == time()) {
                    // date is right now
                    $dates[] = $schedule['startDate'];
                }
            }
        }

        // sort dates in array, lowest first
        usort($dates, function($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $currentAppointment = $dates[0];

        if($this->currentAppointment != $currentAppointment) {
            // current appointment has changed
            $this->currentAppointment = $currentAppointment;
            // remove all old accepts and declines for new one
            $this->appointmentAccepts = [];
            $this->appointmentDeclines = [];
        }


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

    /**
     * @return mixed
     */
    public function getCurrentAppointment()
    {
        return $this->currentAppointment;
    }


    /**
     * @return mixed
     */
    public function getAppointmentAccepts()
    {
        return $this->appointmentAccepts;
    }

    /**
     * @param $appointmentAccept
     */
    public function addAppointmentAccept($appointmentAccept)
    {
        $this->appointmentAccepts[] = $appointmentAccept;
    }

    /**
     * @param $appointmentAccept
     */
    public function removeAppointmentAccept($appointmentAccept)
    {
        if (($key = array_search($appointmentAccept, $this->appointmentAccepts)) !== false) {
            unset($this->appointmentAccepts[$key]);
        }
    }

    /**
     * @return mixed
     */
    public function getAppointmentDeclines()
    {
        return $this->appointmentDeclines;
    }

    /**
     * @param $appointmentDecline
     */
    public function addAppointmentDecline($appointmentDecline)
    {
        $this->appointmentDeclines[] = $appointmentDecline;
    }

    /**
     * @param $appointmentDecline
     */
    public function removeAppointmentDecline($appointmentDecline)
    {
        if (($key = array_search($appointmentDecline, $this->appointmentDeclines)) !== false) {
            unset($this->appointmentDeclines[$key]);
        }
    }

}