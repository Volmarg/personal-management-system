<?php

namespace App\Entity\Modules\Job;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Job\MyJobHolidaysRepository")
 * @ORM\Table(name="my_job_holiday_pool")
 */
class MyJobHolidaysPool {
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
     * @ORM\Column(type="string", length=255)
     */
    private $DaysLeft;

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

    public function getDaysLeft(): ?string {
        return $this->DaysLeft;
    }

    public function setDaysLeft(string $DaysLeft): self {
        $this->DaysLeft = $DaysLeft;

        return $this;
    }

    public function getCompanyName(): ?string {
        return $this->CompanyName;
    }

    public function setCompanyName(string $CompanyName): self {
        $this->CompanyName = $CompanyName;

        return $this;
    }

    public function getDeleted(): ?bool {
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
