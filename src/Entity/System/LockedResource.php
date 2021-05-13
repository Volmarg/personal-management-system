<?php

namespace App\Entity\System;

use App\Entity\Interfaces\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Index;

/**
 * @Table(name="locked_resource",
 *    uniqueConstraints={
 *        @UniqueConstraint(
 *            name="unique_record",
 *            columns={"type", "target", "record"}
 *        )
 *    },
 *    indexes={
 *       @Index(
 *          name="locked_resource_index",
 *          columns={"type", "record", "target"}
 *        )
 *    }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\System\LockedResourceRepository")
 */
class LockedResource implements EntityInterface
{
    const TYPE_ENTITY    = "entity";
    const TYPE_DIRECTORY = "directory";
    const TYPE_URL       = "url";
    const TYPE_MODULE    = "module";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $record;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getRecord(): ?string
    {
        return $this->record;
    }

    public function setRecord(string $record): self
    {
        $this->record = $record;

        return $this;
    }
}
