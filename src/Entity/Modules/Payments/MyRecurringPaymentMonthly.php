<?php

namespace App\Entity\Modules\Payments;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Interfaces\ValidateEntityForCreateInterface;
use App\Entity\Interfaces\ValidateEntityForUpdateInterface;
use App\Entity\Interfaces\ValidateEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MyRecurringPaymentMonthly implements SoftDeletableEntityInterface, EntityInterface, ValidateEntityInterface, ValidateEntityForUpdateInterface, ValidateEntityForCreateInterface
{
    const FIELD_DAYS_OF_MONTH = "dayOfMonth";
    const MIN_DAY_OF_MONTH    = 1;
    const MAX_DAY_OF_MONTH    = 31;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $dayOfMonth;

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

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDayOfMonth(): int {
        return $this->dayOfMonth;
    }

    /**
     * @param int $dayOfMonth
     * @return MyRecurringPaymentMonthly
     */
    public function setDayOfMonth(int $dayOfMonth): self {
        $this->dayOfMonth = $dayOfMonth;

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

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

}
