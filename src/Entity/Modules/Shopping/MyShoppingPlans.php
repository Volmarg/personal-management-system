<?php

namespace App\Entity\Modules\Shopping;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Shopping\MyShoppingPlansRepository")
 * @ORM\Table(name="my_shopping_plan")
 */
class MyShoppingPlans implements SoftDeletableEntityInterface, EntityInterface
{
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
    private $Information;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Example;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->Information;
    }

    public function setInformation(?string $Information): self
    {
        $this->Information = $Information;

        return $this;
    }

    public function getExample(): ?string
    {
        return $this->Example;
    }

    public function setExample(?string $Example): self
    {
        $this->Example = $Example;

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
}
