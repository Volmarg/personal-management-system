<?php

namespace App\Entity\Modules\Payments;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentsOwedRepository")
 * @ORM\Table(name="my_payment_owed")
 */
class MyPaymentsOwed
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
    private $target;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $information;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $owedByMe = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Payments\MyPaymentCurrency", inversedBy="myPaymentOwed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

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

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate($date): self
    {
        if( is_string($date) && !empty($date) ){
            $date = new \DateTime($date);
        }elseif( empty($date) ){
            $date = NULL;
        }

        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOwedByMe(): ?bool
    {
        return $this->owedByMe;
    }

    public function setOwedByMe(bool $owedByMe): self
    {
        $this->owedByMe = $owedByMe;

        return $this;
    }

    public function getCurrency(): ?MyPaymentCurrency
    {
        return $this->currency;
    }

    public function setCurrency(?MyPaymentCurrency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
