<?php

namespace App\Entity\Modules\Schedules;

use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Repository\Modules\Schedules\MyScheduleReminderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MyScheduleReminderRepository::class)
 */
class MyScheduleReminder implements SoftDeletableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=MySchedule::class, inversedBy="myScheduleReminders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schedule;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="datetime", unique=true)
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchedule(): ?MySchedule
    {
        return $this->schedule;
    }

    public function setSchedule(?MySchedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }
}
