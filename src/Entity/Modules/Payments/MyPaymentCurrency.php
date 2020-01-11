<?php

namespace App\Entity\Modules\Payments;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentCurrencyRepository")
 */
class MyPaymentCurrency
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
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $symbol;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Payments\MyPaymentsOwed", mappedBy="currency")
     */
    private $myPaymentOwed;

    public function __construct()
    {
        $this->myPaymentOwed = new ArrayCollection();
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|MyPaymentsOwed[]
     */
    public function getMyPaymentOwed(): Collection
    {
        return $this->myPaymentOwed;
    }

    public function addMyPaymentOwed(MyPaymentsOwed $myPaymentOwed): self
    {
        if (!$this->myPaymentOwed->contains($myPaymentOwed)) {
            $this->myPaymentOwed[] = $myPaymentOwed;
            $myPaymentOwed->setCurrency($this);
        }

        return $this;
    }

    public function removeMyPaymentOwed(MyPaymentsOwed $myPaymentOwed): self
    {
        if ($this->myPaymentOwed->contains($myPaymentOwed)) {
            $this->myPaymentOwed->removeElement($myPaymentOwed);
            // set the owning side to null (unless already changed)
            if ($myPaymentOwed->getCurrency() === $this) {
                $myPaymentOwed->setCurrency(null);
            }
        }

        return $this;
    }
}
