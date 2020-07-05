<?php

namespace App\Entity\Modules\Payments;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentsBillsRepository")
 * @ORM\Table(name="my_payment_bill")
 */
class MyPaymentsBills implements SoftDeletableEntityInterface, EntityInterface
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
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $information;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Payments\MyPaymentsBillsItems", mappedBy="bill")
     */
    private $item;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $plannedAmount;

    public function __construct()
    {
        $this->item = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate($startDate): self
    {

        if (is_string($startDate)) {
            $startDate = new \DateTime($startDate);
        }

        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate($endDate): self
    {

        if (is_string($endDate)) {
            $endDate = new \DateTime($endDate);
        }

        $this->endDate = $endDate;

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

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): self
    {
        $this->information = $information;

        return $this;
    }

    /**
     * @return Collection|MyPaymentsBillsItems[]
     */
    public function getItem(): Collection
    {
        return $this->item;
    }

    public function addItem(MyPaymentsBillsItems $item): self
    {
        if (!$this->item->contains($item)) {
            $this->item[] = $item;
            $item->setBill($this);
        }

        return $this;
    }

    public function removeItem(MyPaymentsBillsItems $item): self
    {
        if ($this->item->contains($item)) {
            $this->item->removeElement($item);
            // set the owning side to null (unless already changed)
            if ($item->getBill() === $this) {
                $item->setBill(null);
            }
        }

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

    /**
     * @return mixed
     */
    public function getPlannedAmount() {
        return $this->plannedAmount;
    }

    /**
     * @param mixed $plannedAmount
     */
    public function setPlannedAmount($plannedAmount): void {
        $this->plannedAmount = $plannedAmount;
    }

}
