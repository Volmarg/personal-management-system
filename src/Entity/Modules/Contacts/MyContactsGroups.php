<?php

namespace App\Entity\Modules\Contacts;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Contacts\MyContactsGroupsRepository")
 */
class MyContactsGroups
{
    const TYPE_ARCHIVED = 'archived';
    const TYPE_PHONE    = 'phone';
    const TYPE_OTHER    = 'other';
    const TYPE_EMAIL    = 'email';

    const ALL_TYPES = [
        self::TYPE_ARCHIVED,
        self::TYPE_PHONE,
        self::TYPE_OTHER,
        self::TYPE_EMAIL,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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
}
