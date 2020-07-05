<?php

namespace App\Entity\Modules\Achievements;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Achievements\AchievementRepository")
 */
class Achievement implements SoftDeletableEntityInterface, EntityInterface {

    public const ENUM_SIMPLE = 'simple';
    public const ENUM_MEDIUM = 'medium';
    public const ENUM_HARD = 'hard';
    public const ENUM_HARDCORE = 'hardcore';

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
    private $Description;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('simple', 'medium', 'hard', 'hardcore')", nullable=false)
     */
    private $Type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

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

    public function getDescription(): ?string {
        return $this->Description;
    }

    public function setDescription(?string $Description): self {
        $this->Description = $Description;

        return $this;
    }

    public function getType(): ?string {
        return $this->Type;
    }

    public function setType(string $Type): self {
        $this->Type = $Type;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }
}
