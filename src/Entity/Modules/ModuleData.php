<?php

namespace App\Entity\Modules;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\System\LockedResource;
use App\Repository\Modules\ModuleDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 *
 * This entity contain overall data that can be supplied for given modules / entries
 * Should contain mostly things like descriptions, headers, etc.
 *
 * No global mechanism logic should be implemented here.
 * Like for example the @see LockedResource is an separate being despite the fact that it could be implemented here
 *
 * @Table(name="module_data",
 *    uniqueConstraints={
 *        @UniqueConstraint(
 *            name="unique_record",
 *            columns={"record_type", "module", "record_identifier"}
 *        )
 *    },
 *    indexes={
 *       @Index(
 *          name="module_data_index",
 *          columns={"id", "record_type", "module", "record_identifier"}
 *        )
 *    }
 * )
 * @ORM\Entity(repositoryClass=ModuleDataRepository::class)
 */
class ModuleData implements EntityInterface
{
    const FIELD_NAME_ID                = "id";
    const FIELD_NAME_RECORD_TYPE       = "recordType";
    const FIELD_NAME_MODULE            = "module";
    const FIELD_NAME_RECORD_IDENTIFIER = "recordIdentifier";

    const RECORD_TYPE_DIRECTORY = "directory";
    const RECORD_TYPE_MODULE    = "module";
    const RECORD_TYPE_ENTITY    = "entity";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, name="record_type")
     */
    private $recordType;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $module;

    /**
     * @ORM\Column(type="string", length=255, name="record_identifier")
     */
    private $recordIdentifier;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordType(): ?string
    {
        return $this->recordType;
    }

    public function setRecordType(string $recordType): self
    {
        $this->recordType = $recordType;

        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getRecordIdentifier(): ?string
    {
        return $this->recordIdentifier;
    }

    public function setRecordIdentifier(string $recordIdentifier): self
    {
        $this->recordIdentifier = $recordIdentifier;

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
}
