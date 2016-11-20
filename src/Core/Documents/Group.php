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
    protected $currentSchedule;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $created;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $modified;


    public function __construct()
    {
        $this->setCreated(new \DateTime());
        if ($this->getModified() == null) {
            $this->setModified(new \DateTime());
        }
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
     * @todo we should add unit tests for this
     */
    private function setCurrentSchedule()
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
        $currentSchedule = null;
        $time = date('H:i:s');
        $now = new \DateTime();
        $today = date('w');
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
                                $dates[] = $nextWeekday . ' ' . $schedule['time'];
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

            // sort dates in array, lowest first
            usort($dates, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });

            $currentSchedule = $dates[0];
        }


        if ($this->currentSchedule != $currentSchedule) {
            // current schedule has changed
            $this->currentSchedule = $currentSchedule;
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
     * @param $scheduleId
     * @return mixed $schedule
     */
    public function getSchedule($scheduleId)
    {
        $schedule = null;

        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            $schedule = $this->schedules[$key];
        }

        return $schedule;
    }

    /**
     * @param $schedule
     */
    public function addSchedule($schedule)
    {
        $schedule['accepts'] = [];
        $schedule['declines'] = [];
        $this->schedules[] = $schedule;
    }

    /**
     * @param $scheduleId
     */
    public function removeSchedule($scheduleId)
    {
        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            unset($this->schedules[$key]);
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentSchedule()
    {
        return $this->currentSchedule;
    }

    /**
     * @param $userId
     * @param $scheduleId
     */
    public function addScheduleAccept($userId, $scheduleId)
    {
        // delete old entries first
        $this->removeScheduleDecline($userId, $scheduleId);
        $this->removeScheduleAccept($userId, $scheduleId);
        // add entry
        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            $this->schedules[$key]['accepts'][] = $userId;
        }
    }

    /**
     * @param $userId
     * @param  $scheduleId
     */
    public function removeScheduleAccept($userId, $scheduleId)
    {
        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            if(!empty($this->schedules[$key]['accepts'])){
                $arr = array_diff($this->schedules[$key]['accepts'], array($userId));
                $this->schedules[$key]['accepts'] = array_values($arr);
            }
        }
    }

    /**
     * @param $userId
     * @param  $scheduleId
     */
    public function addScheduleDecline($userId, $scheduleId)
    {
        // delete old entries first
        $this->removeScheduleDecline($userId, $scheduleId);
        $this->removeScheduleAccept($userId, $scheduleId);
        // add entry
        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            $this->schedules[$key]['declines'][] = $userId;
        }
    }

    /**
     * @param $userId
     * @param  $scheduleId
     */
    public function removeScheduleDecline($userId, $scheduleId)
    {
        if (($key = array_search($scheduleId, array_column($this->schedules, 'id'))) !== false) {
            if(!empty($this->schedules[$key]['declines'])){
                $arr = array_diff($this->schedules[$key]['declines'], array($userId));
                $this->schedules[$key]['declines'] = array_values($arr);
            }

        }
    }

    /**
     * @return \DateTime $date
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $date
     */
    public function setCreated(\DateTime $date)
    {
        $this->created = $date;
    }

    /**
     * @return \DateTime $date
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $date
     */
    public function setModified(\DateTime $date)
    {
        $this->modified = $date;
    }


    /**
     * @MongoDB\PrePersist
     * @MongoDB\PreUpdate
     */
    public function handlePreActions()
    {
        $this->setCurrentSchedule();
        $this->updateModifiedDatetime();
    }


    /**
     * set modified date
     */
    private function updateModifiedDatetime()
    {
        // update the modified time
        $this->setModified(new \DateTime());
    }

}