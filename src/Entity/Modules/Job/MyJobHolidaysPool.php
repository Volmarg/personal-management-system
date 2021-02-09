<?php

namespace App\Entity\Modules\Job;

use App\Entity\Interfaces\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Job\MyJobHolidaysPoolRepository")
 * @ORM\Table(name="my_job_holiday_pool")
 */
class MyJobHolidaysPool implements EntityInterface{

    const FIELD_DAYS_IN_POOL = "days_in_pool";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, name="days_in_pool")
     */
    private $daysInPool;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CompanyName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Frozen = 0;

    public function getId(): ?int {
        return $this->id;
    }

    public function getYear(): ?string {
        return $this->year;
    }

    public function setYear(string $year): self {
        $this->year = $year;

        return $this;
    }

    public function getDaysInPool(): ?string {
        return $this->daysInPool;
    }

    public function setDaysInPool(string $daysInPool): self {
        $this->daysInPool = $daysInPool;

        return $this;
    }

    public function getCompanyName(): ?string {
        return $this->CompanyName;
    }

    public function setCompanyName(string $CompanyName): self {
        $this->CompanyName = $CompanyName;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    public function getFrozen(): ?bool {
        return $this->Frozen;
    }

    public function setFrozen(bool $Frozen): self {
        $this->Frozen = $Frozen;

        return $this;
    }
}
