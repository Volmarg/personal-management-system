<?php

namespace App\Entity\Modules\Notes;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Notes\MyNotesRepository")
 * @ORM\Table(name="my_note")
 */
class MyNotes implements SoftDeletableEntityInterface, EntityInterface
{
    const KEY_DELETED = "deleted";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Body;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Notes\MyNotesCategories", inversedBy="note")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): ?string {
        return $this->Title;
    }

    public function setTitle(string $Title): self {
        $this->Title = $Title;

        return $this;
    }

    public function getBody(): ?string {
        return $this->Body;
    }

    public function setBody(?string $Body): self {
        $this->Body = $Body;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCategory(): ?MyNotesCategories {
        return $this->category;
    }

    public function setCategory(?MyNotesCategories $category): self {
        $this->category = $category;

        return $this;
    }
}
