<?php

namespace App\Entity\Modules\Contacts;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Contacts\MyContactsRepository")
 */
class MyContacts {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Contacts\MyContactsGroups")
     */
    private $group;

    public function getId(): ?int {
        return $this->id;
    }

    public function getContact(): ?string {
        return $this->contact;
    }

    public function setContact(string $contact): self {
        $this->contact = $contact;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): self {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(string $type): self {
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

    /**
     * @return MyContactsGroups
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @param MyContactsGroups $group
     * @return MyContacts
     */
    public function setGroup($group): self {
        $this->group = $group;

        return $this;
    }


}
