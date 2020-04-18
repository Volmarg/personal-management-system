<?php

namespace App\Entity\Modules\Schedules;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Schedules\MyScheduleTypeRepository")
 */
class MyScheduleType
{
    const FIELD_NAME = "deleted";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icon;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Schedules\MySchedule", mappedBy="scheduleType")
     */
    private $mySchedule;

    public function __construct()
    {
        $this->mySchedule = new ArrayCollection();
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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon($icon): void {
        $this->icon = $icon;
    }

    /**
     * @return Collection|MySchedule[]
     */
    public function getMySchedule(): Collection
    {
        return $this->mySchedule;
    }

    public function addMySchedule(MySchedule $mySchedule): self
    {
        if (!$this->mySchedule->contains($mySchedule)) {
            $this->mySchedule[] = $mySchedule;
            $mySchedule->setScheduleType($this);
        }

        return $this;
    }

    public function removeMySchedule(MySchedule $mySchedule): self
    {
        if ($this->mySchedule->contains($mySchedule)) {
            $this->mySchedule->removeElement($mySchedule);
            // set the owning side to null (unless already changed)
            if ($mySchedule->getScheduleType() === $this) {
                $mySchedule->setScheduleType(null);
            }
        }

        return $this;
    }
}
