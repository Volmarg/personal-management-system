<?php

namespace App\Entity\Modules\Health;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Trait\CreateModifyFieldAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Health\DoctorRepository")
 * @ORM\Table(name="doctor", uniqueConstraints={
 *      @UniqueConstraint(
 *        name="unique_record",
 *        columns={"specialisation", "name"}
 *      )
 *    })
 */
class Doctor implements EntityInterface
{
    use CreateModifyFieldAwareTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $specialisation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $address;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $information = null;

    public function __construct()
    {
        $this->setCreatedModified();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getSpecialisation(): string
    {
        return $this->specialisation;
    }

    public function setSpecialisation(string $specialisation): void
    {
        $this->specialisation = $specialisation;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): void
    {
        $this->information = $information;
    }
}