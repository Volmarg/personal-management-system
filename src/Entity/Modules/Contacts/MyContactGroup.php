<?php

namespace App\Entity\Modules\Contacts;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Contacts\MyContactGroupRepository")
 */
class MyContactGroup implements SoftDeletableEntityInterface, EntityInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $color;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Contacts\MyContact", mappedBy="group")
     */
    private $myContacts;

    public function __construct()
    {
        $this->myContacts = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getColor() {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void {
        $this->color = $color;
    }

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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void {
        $this->icon = $icon;
    }

    /**
     * @return Collection|MyContact[]
     */
    public function getMyContacts(): Collection
    {
        return $this->myContacts;
    }

    public function addMyContact(MyContact $myContact): self
    {
        if (!$this->myContacts->contains($myContact)) {
            $this->myContacts[] = $myContact;
            $myContact->setGroup($this);
        }

        return $this;
    }

    public function removeMyContact(MyContact $myContact): self
    {
        if ($this->myContacts->contains($myContact)) {
            $this->myContacts->removeElement($myContact);
            // set the owning side to null (unless already changed)
            if ($myContact->getGroup() === $this) {
                $myContact->setGroup(null);
            }
        }

        return $this;
    }

}
