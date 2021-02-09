<?php

namespace App\Entity\Modules\Notes;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index; 

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Notes\MyNotesCategoriesRepository")
 * @ORM\Table(name="my_note_category",
 *    indexes={
 *       @Index(
 *          name="my_note_category_index",
 *          columns={"id"}
 *        )
 *    }
 * )
 *
 */
class MyNotesCategories implements SoftDeletableEntityInterface, EntityInterface
{

    const KEY_DELETED = "deleted";
    const KEY_NAME    = "name";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Notes\MyNotes", mappedBy="category")
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parentId = NULL;

    /**
     * @return mixed
     */
    public function getParentId() {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     * @throws \Exception
     */
    public function setParentId($parentId): void {
        if (
                $this->id == $parentId
            &&  !is_null($parentId)
        ) {
            throw new \Exception('You cannot be children and parent at the same time!');
        }
        $this->parentId = $parentId;
    }

    /**
     * Fix for usage in FormType as EntityType, without it Entity type crashes
     */
    public function __toString() {
        return strval($this->id);
    }

    public function __construct() {
        $this->note = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }
    
    
    public function setId(int $id): self{
         $this->id = $id;
         return $this;
    }

    public function getIcon(): ?string {
        return $this->icon;
    }

    public function setIcon(?string $icon): self {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection|MyNotes[]
     */
    public function getNote(): Collection {
        return $this->note;
    }

    public function addNote(MyNotes $note): self {
        if (!$this->note->contains($note)) {
            $this->note[] = $note;
            $note->setCategory($this);
        }

        return $this;
    }

    public function removeNote(MyNotes $note): self {
        if ($this->note->contains($note)) {
            $this->note->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getCategory() === $this) {
                $note->setCategory(null);
            }
        }

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    public function getColor(): ?string {
        return $this->color;
    }

    public function setColor(string $color): self {
        $this->color = strtoupper(str_replace('#', '', $color));

        return $this;
    }
}
