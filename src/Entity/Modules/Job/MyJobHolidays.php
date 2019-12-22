<?php

namespace App\Entity\Modules\Job;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Job\MyJobHolidaysRepository")
 * @ORM\Table(name="my_job_holiday")
 */
class MyJobHolidays {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $daysSpent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $information;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    public function getId(): ?int {
        return $this->id;
    }

    public function getDaysSpent(): ?int {
        return $this->daysSpent;
    }

    public function setDaysSpent(int $daysSpent): self {
        $this->daysSpent = $daysSpent;

        return $this;
    }

    public function getInformation(): ?string {
        return $this->information;
    }

    public function setInformation(string $information): self {
        $this->information = $information;

        return $this;
    }

    public function getYear(): ?int {
        return $this->year;
    }

    public function setYear(int $year): self {
        $this->year = $year;

        return $this;
    }

    public function getDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

}
