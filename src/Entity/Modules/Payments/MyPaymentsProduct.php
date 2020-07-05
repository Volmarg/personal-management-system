<?php

namespace App\Entity\Modules\Payments;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentsProductRepository")
 * @ORM\Table(name="my_payment_product")
 */
class MyPaymentsProduct implements SoftDeletableEntityInterface, EntityInterface
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
     * @ORM\Column(type="string", length=255)
     */
    private $Price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Market;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Products;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Information;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $Rejected;

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

    public function getPrice(): ?string
    {
        return $this->Price;
    }

    public function setPrice(string $Price): self
    {
        $this->Price = $Price;

        return $this;
    }

    public function getMarket(): ?string
    {
        return $this->Market;
    }

    public function setMarket(?string $Market): self
    {
        $this->Market = $Market;

        return $this;
    }

    public function getProducts(): ?string
    {
        return $this->Products;
    }

    public function setProducts(?string $Products): self
    {
        $this->Products = $Products;

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

    public function getRejected(): ?bool
    {
        return $this->Rejected;
    }

    public function setRejected(?bool $Rejected): self
    {
        $this->Rejected = $Rejected;

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
