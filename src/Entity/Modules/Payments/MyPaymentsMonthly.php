<?php

namespace App\Entity\Modules\Payments;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyPaymentsMonthlyRepository")
 * @ORM\Table(name="my_payment_monthly")
 */
class MyPaymentsMonthly implements SoftDeletableEntityInterface, EntityInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", length=255, nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $money;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Payments\MyPaymentsSettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return MyPaymentsMonthly
     * @throws \Exception
     */
    public function setDate($date): self {

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $this->date = $date;

        return $this;

    }

    public function getMoney(): ?float
    {
        return $this->money;
    }

    public function setMoney(float $money): self
    {
        $this->money = $money;

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

    public function getType(): ?MyPaymentsSettings
    {
        return $this->Type;
    }

    public function setType(?MyPaymentsSettings $Type): self
    {
        $this->Type = $Type;

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
