<?php

namespace App\Entity\Modules\Payments\Monthly\Import;

use App\Entity\Interfaces\EntityInterface;
use App\Enum\Modules\Payments\Monthly\ImportFieldEnum;
use App\Enum\Modules\Payments\Monthly\ImportRuleTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="my_payment_monthly_import_filter_rule")
 */
class ImportFilterRule implements EntityInterface
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
    private string $fieldName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $rule;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        ImportFieldEnum::from($fieldName);
        $this->fieldName = $fieldName;
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function setRule(string $rule): void
    {
        $this->rule = $rule;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        ImportRuleTypeEnum::from($type);
        $this->type = $type;
    }

}
