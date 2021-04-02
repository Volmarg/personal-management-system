<?php

namespace App\Entity\Modules\Schedules;

use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Repository\Modules\Schedules\MyScheduleReminderRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(uniqueConstraints={
 *     @UniqueConstraint(
 *       name="unique_record",
 *       columns={"schedule_id", "date"}
 *     )
 *   }
 * )
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
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @var bool $processed
     */
    private bool $processed = false;

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

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     */
    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

}
