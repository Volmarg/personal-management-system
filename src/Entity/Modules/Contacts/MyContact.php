<?php

namespace App\Entity\Modules\Contacts;

use App\DTO\Modules\Contacts\ContactsTypesDTO;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Contacts\MyContactRepository")
 */
class MyContact implements SoftDeletableEntityInterface, EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", columnDefinition="LONGTEXT NOT NULL")
     */
    private $contacts;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imagePath;

    /**
     * @ORM\Column(type="text")
     */
    private $nameBackgroundColor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descriptionBackgroundColor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Contacts\MyContactGroup", inversedBy="myContacts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $group;

    /**
     * todo: check what happens if there are no contacts for this entry
     * Contacts in DB are stored as JSON bo we need dto to work with the data
     * @return ContactsTypesDTO
     * @throws \Exception
     */
    public function getContacts() {

        $json = $this->contacts;
        $dto = ContactsTypesDTO::fromJson($json);

        return $dto;
    }

    /**
     * @param string $contacts (json)
     */
    public function setContacts(string $contacts): void {
        $this->contacts = $contacts;
    }

    /**
     * @return string
     */
    public function getNameBackgroundColor() {
        return $this->nameBackgroundColor;
    }

    /**
     * @param mixed $nameBackgroundColor
     */
    public function setNameBackgroundColor($nameBackgroundColor): void {
        $this->nameBackgroundColor = $nameBackgroundColor;
    }

    /**
     * @return string
     */
    public function getDescriptionBackgroundColor() {
        return $this->descriptionBackgroundColor;
    }

    /**
     * @param string $descriptionBackgroundColor
     */
    public function setDescriptionBackgroundColor($descriptionBackgroundColor): void {
        $this->descriptionBackgroundColor = $descriptionBackgroundColor;
    }

    /**
     * @return string
     */
    public function getImagePath() {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath): void {
        $this->imagePath = $imagePath;
    }

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void {
        $this->id = $id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MyContact
     */
    public function setName(string $name): self {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @param string $description
     * @return MyContact
     */
    public function setDescription(?string $description): self {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return MyContact
     */
    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    public function getGroup(): ?MyContactGroup
    {
        return $this->group;
    }

    public function setGroup(?MyContactGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

}
