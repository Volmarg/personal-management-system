<?php

namespace App\Entity\Modules\Schedules;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Schedules\MyScheduleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MySchedule {
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
     * @ORM\Column(type="date", length=255, nullable=true)
     */
    private $Date;

    /**
     * @ORM\Column(type="integer")
     * @var bool $isDateBased
     */
    private $isDateBased = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Information;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Schedules\MyScheduleType", inversedBy="mySchedule")
     * @ORM\JoinColumn(nullable=true)
     */
    private $scheduleType;

    /**
     * @return bool
     */
    public function isDateBased(): bool {
        return $this->isDateBased;
    }

    /**
     * This function will be called after entity is persisted
     * Must be public for CallbackEvent
     * @ORM\PreFlush
     */
    public function setIsDateBased(): self {

        if( $this->getDate() instanceof \DateTime ){
            $this->isDateBased = true;
        }

        return $this;
    }

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

    public function getDate() {
        return $this->Date;
    }

    /**
     * @param $Date
     * @return MySchedule
     * @throws \Exception
     */
    public function setDate($Date): self {

        if( is_string($Date) ){
            $Date = new \DateTime($Date);
        }

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

    public function getScheduleType(): ?MyScheduleType
    {
        return $this->scheduleType;
    }

    public function setScheduleType(?MyScheduleType $scheduleType): self
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }
}
