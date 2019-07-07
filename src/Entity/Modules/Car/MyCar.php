<?php

namespace App\Entity\Modules\Car;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Car\MyCarRepository")
 */
class MyCar {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Information;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Car\MyCarSchedulesTypes", inversedBy="myCars")
     * @ORM\JoinColumn(nullable=true)
     */
    private $scheduleType;

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->Name;
    }

    public function setName(string $Name): self {
        $this->Name = $Name;

        return $this;
    }

    public function getDate(): ?string {
        return $this->Date;
    }

    public function setDate(string $Date): self {
        $this->Date = $Date;

        return $this;
    }

    public function getInformation(): ?string {
        return $this->Information;
    }

    public function setInformation(?string $Information): self {
        $this->Information = $Information;

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

    public function getScheduleType(): ?MyCarSchedulesTypes
    {
        return $this->scheduleType;
    }

    public function setScheduleType(?MyCarSchedulesTypes $scheduleType): self
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }
}
