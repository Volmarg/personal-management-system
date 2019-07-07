<?php

namespace App\Entity\Modules\Job;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Job\MyJobAfterhoursRepository")
 */
class MyJobAfterhours {

    public const ENUM_SPENT = 'spent';
    public const ENUM_MADE = 'made';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Description;

    /**
     * @ORM\Column(type="integer", length=100)
     */
    private $Minutes;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('spent', 'made')", nullable=false)
     */
    private $Type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $goal;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    public function getId(): ?int {
        return $this->id;
    }

    public function getDate(): ?string {
        return $this->Date;
    }

    public function setDate(string $Date): self {
        $this->Date = $Date;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->Description;
    }

    public function setDescription(string $Description): self {
        $this->Description = $Description;

        return $this;
    }

    public function getMinutes(): ?int {
        return $this->Minutes;
    }

    public function setMinutes(int $Minutes): self {
        $this->Minutes = $Minutes;

        return $this;
    }

    public function getType(): ?string {
        return $this->Type;
    }

    public function setType(string $Type): self {
        $this->Type = $Type;

        return $this;
    }

    public function getGoal(): ?string {
        return $this->goal;
    }

    public function setGoal(?string $goal): self {
        $this->goal = (empty($goal) ? NULL : $goal);

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
