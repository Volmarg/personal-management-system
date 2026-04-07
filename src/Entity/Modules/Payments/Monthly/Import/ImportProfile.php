<?php

namespace App\Entity\Modules\Payments\Monthly\Import;

use App\Entity\Interfaces\EntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="my_payment_monthly_import_profile")
 */
class ImportProfile implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dateField = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $moneyField = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $descriptionField = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $currencyField = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Payments\Monthly\Import\ImportFilterRule", mappedBy="importProfile")
     */
    private $filterRules;

    public function __construct()
    {
        $this->filterRules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDateField(): ?string
    {
        return $this->dateField;
    }

    public function setDateField(?string $dateField): void
    {
        $this->dateField = $dateField;
    }

    public function getMoneyField(): ?string
    {
        return $this->moneyField;
    }

    public function setMoneyField(?string $moneyField): void
    {
        $this->moneyField = $moneyField;
    }

    public function getDescriptionField(): ?string
    {
        return $this->descriptionField;
    }

    public function setDescriptionField(?string $descriptionField): void
    {
        $this->descriptionField = $descriptionField;
    }

    public function getCurrencyField(): ?string
    {
        return $this->currencyField;
    }

    public function setCurrencyField(?string $currencyField): void
    {
        $this->currencyField = $currencyField;
    }

    public function getFilterRules(): ArrayCollection
    {
        return $this->filterRules;
    }

    public function setFilterRules(ArrayCollection $filterRules): void
    {
        $this->filterRules = $filterRules;
    }

}
