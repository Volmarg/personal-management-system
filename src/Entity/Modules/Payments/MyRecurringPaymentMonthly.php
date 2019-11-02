<?php

namespace App\Entity\Modules\Payments;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MyRecurringPaymentMonthly {

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
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Payments\MyPaymentsSettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * This field is used with cron to check if the hash value for given month is already in database
     * @ORM\Column(type="string")
     */
    private $hash;

    public function getId(): ?int {
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
     * @return MyRecurringPaymentMonthly
     * @throws \Exception
     */
    public function setDate($date): self {

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $this->date = $date;

        return $this;

    }

    public function getMoney(): ?float {
        return $this->money;
    }

    public function setMoney(float $money): self {
        $this->money = $money;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): self {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?MyPaymentsSettings {
        return $this->type;
    }

    public function setType(?MyPaymentsSettings $type): self {
        $this->type = $type;

        return $this;
    }

    public function getDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    public function getHash(): ?string {
        return $this->hash;
    }

    /**
     * This function will be called after entity is persisted
     * Must be public for CallbackEvent
     * We set hash based on description and date to ensure that data of given description and date is in DB (cron use)
     * @ORM\PreFlush
     */
    public function setHash(): self {

        $hash = $this->calculateHash();

        $this->hash = $hash;

        return $this;
    }

    public function isHashEqual($hash): bool {
        return ($this->hash === $hash);
    }

    public function calculateHash(){
        if( $this->date instanceof \DateTime ){
            $date = $this->date->format('Y-m-d');
        }else{
            $date = $this->date;
        }

        $string = $date . $this->description;

        $hash = sha1($string);

        return $hash;
    }

}
