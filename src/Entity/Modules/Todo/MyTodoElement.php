<?php

namespace App\Entity\Modules\Todo;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Todo\MyTodoElementRepository")
 * @ORM\Table(name="my_todo_element")
 */
class MyTodoElement implements SoftDeletableEntityInterface, EntityInterface
{
    const FIELD_NAME = "name";
    const FIELD_TODO = "myTodo";

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
    private $name = "";

    /**
     * @var bool $deleted
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @var boolean $completed
     * @ORM\Column(type="boolean")
     */
    private $completed = 0;

    /**
     * @ORM\ManyToOne(targetEntity=MyTodo::class, inversedBy="myTodoElement")
     * @ORM\JoinColumn(nullable=false)
     */
    private $myTodo;

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

    public function getMyTodo(): ?MyTodo
    {
        return $this->myTodo;
    }

    public function setMyTodo(?MyTodo $myTodo): self
    {
        $this->myTodo = $myTodo;

        return $this;
    }

}
