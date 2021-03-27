<?php

namespace App\Entity\Modules\Schedules;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Repository\Modules\Schedules\MyScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Exception;

/**
 * @ORM\Entity(repositoryClass=MyScheduleRepository::class)
 */
class MySchedule implements SoftDeletableEntityInterface, EntityInterface
{
    const FIELD_NAME_DELETED = "deleted";
    const CATEGORY_ALL_DAY   = "allday";
    const CATEGORY_TIME      = "time";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $body;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allDay;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @ORM\ManyToOne(targetEntity=MyScheduleCalendar::class, inversedBy="schedules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $calendar;

    /**
     * @ORM\OneToMany(targetEntity=MyScheduleReminder::class, mappedBy="schedule", orphanRemoval=true)
     */
    private $myScheduleReminders;

    public function __construct()
    {
        $this->myScheduleReminders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCalendar(): ?MyScheduleCalendar
    {
        return $this->calendar;
    }

    public function setCalendar(?MyScheduleCalendar $calendar): self
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return MyScheduleReminder[]
     */
    public function getMyScheduleReminders(): array
    {
        if( $this->myScheduleReminders instanceof PersistentCollection ){
            return $this->myScheduleReminders->getValues();
        }

        return $this->myScheduleReminders;
    }

    /**
     * @param MyScheduleReminder $myScheduleReminder
     * @return $this
     */
    public function addMyScheduleReminder(MyScheduleReminder $myScheduleReminder): self
    {
        if (!$this->myScheduleReminders->contains($myScheduleReminder)) {
            $this->myScheduleReminders[] = $myScheduleReminder;
            $myScheduleReminder->setSchedule($this);
        }

        return $this;
    }

    /**
     * Will set reminders in the schedule
     *
     * @param MyScheduleReminder[] $reminders
     */
    public function setMyScheduleReminders(array $reminders)
    {
        $this->myScheduleReminders = $reminders;
    }

    /**
     * @param MyScheduleReminder $myScheduleReminder
     * @return $this
     */
    public function removeMyScheduleReminder(MyScheduleReminder $myScheduleReminder): self
    {
        if ($this->myScheduleReminders->removeElement($myScheduleReminder)) {
            // set the owning side to null (unless already changed)
            if ($myScheduleReminder->getSchedule() === $this) {
                $myScheduleReminder->setSchedule(null);
            }
        }

        return $this;
    }

    /**
     * Returns an array where key is id of entity and value is the date
     * @return array
     * @throws Exception
     */
    public function getRemindersDatesWithIds(): array
    {
        $datesWithIds = [];
        foreach($this->getMyScheduleReminders() as $reminder){

            if( empty($reminder->getId()) ){
                throw new Exception("This method should not be called in given case as the entity id was not yet set");
            }

            $datesWithIds[$reminder->getId()] = $reminder->getDate()->format("Y-m-d H:i:s");
        }

        return $datesWithIds;
    }

}
