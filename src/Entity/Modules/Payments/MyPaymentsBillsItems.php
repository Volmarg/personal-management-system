<?php

namespace App\Entity\Modules\Payments;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentsBillsItemsRepository")
 * @ORM\Table(name="my_payment_bill_item")
 */
class MyPaymentsBillsItems implements SoftDeletableEntityInterface, EntityInterface
{
    const FIELD_DELETED = "deleted";
    const FIELD_ID      = "id";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Payments\MyPaymentsBills", inversedBy="item")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bill;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
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

    public function getBill(): ?MyPaymentsBills
    {
        return $this->bill;
    }

    public function setBill(?MyPaymentsBills $bill): self
    {
        $this->bill = $bill;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate($date): self
    {

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $this->date = $date;

        return $this;
    }

}
