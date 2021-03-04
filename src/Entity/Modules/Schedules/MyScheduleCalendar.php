<?php

namespace App\Entity\Modules\Schedules;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Repository\Modules\Schedules\MyScheduleCalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MyScheduleCalendarRepository::class)
 */
class MyScheduleCalendar implements SoftDeletableEntityInterface, EntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $backgroundColor;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $dragBackgroundColor;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $borderColor;

    /**
     * @ORM\OneToMany(targetEntity=MySchedule::class, mappedBy="calendar")
     */
    private $schedules;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $icon;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = strtoupper(str_replace('#', '', $color));

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = strtoupper(str_replace('#', '', $backgroundColor));

        return $this;
    }

    public function getDragBackgroundColor(): ?string
    {
        return $this->dragBackgroundColor;
    }

    public function setDragBackgroundColor(string $dragBackgroundColor): self
    {
        $this->dragBackgroundColor = strtoupper(str_replace('#', '', $dragBackgroundColor));

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(string $borderColor): self
    {
        $this->borderColor = strtoupper(str_replace('#', '', $borderColor));

        return $this;
    }

    /**
     * @return Collection|MySchedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(MySchedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setCalendar($this);
        }

        return $this;
    }

    public function removeSchedule(MySchedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getCalendar() === $this) {
                $schedule->setCalendar(null);
            }
        }

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
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

}
