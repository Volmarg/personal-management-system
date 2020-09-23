<?php

namespace App\Entity\Modules\Todo;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\System\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Todo\MyTodoRepository")
 * @ORM\Table(name="my_todo")
 */
class MyTodo implements SoftDeletableEntityInterface, EntityInterface
{
    const FIELD_DESCRIPTION          = "description";
    const FIELD_DISPLAY_ON_DASHBOARD = "displayOnDashboard";
    const FIELD_NAME                 = "name";
    const FIELD_DELETED              = "deleted";
    const FIELD_MODULE               = "module";

    /**
     * @var int $id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $name
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var boolean $deleted
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @var boolean $completed
     * @ORM\Column(type="boolean")
     */
    private $completed = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayOnDashboard = 0;

    /**
     * @ORM\OneToOne(targetEntity=Module::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $module;

    /**
     * @ORM\OneToMany(targetEntity=MyTodoElement::class, mappedBy="myTodo")
     */
    private $myTodoElement;

    /**
     * This is only help field to build later relation with given module
     *
     * @var int $relatedEntityId
     */
    private $relatedEntityId = 0;

    public function __construct()
    {
        $this->myTodoElement = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function getDisplayOnDashboard(): ?bool
    {
        return $this->displayOnDashboard;
    }

    public function setDisplayOnDashboard(bool $displayOnDashboard): self
    {
        $this->displayOnDashboard = $displayOnDashboard;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return Collection|MyTodoElement[]
     */
    public function getMyTodoElement(): Collection
    {
        return $this->myTodoElement;
    }

    public function addMyTodoElement(MyTodoElement $myTodoElement): self
    {
        if (!$this->myTodoElement->contains($myTodoElement)) {
            $this->myTodoElement[] = $myTodoElement;
            $myTodoElement->setMyTodo($this);
        }

        return $this;
    }

    public function removeMyTodoElement(MyTodoElement $myTodoElement): self
    {
        if ($this->myTodoElement->contains($myTodoElement)) {
            $this->myTodoElement->removeElement($myTodoElement);
            // set the owning side to null (unless already changed)
            if ($myTodoElement->getMyTodo() === $this) {
                $myTodoElement->setMyTodo(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getRelatedEntityId(): int {
        return $this->relatedEntityId;
    }

    /**
     * @param int $relatedEntityId
     */
    public function setRelatedEntityId(int $relatedEntityId): void {
        $this->relatedEntityId = $relatedEntityId;
    }

}
