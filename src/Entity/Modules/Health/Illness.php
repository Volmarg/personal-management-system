<?php

namespace App\Entity\Modules\Health;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Trait\CreateModifyFieldAwareTrait;
use App\Entity\Trait\SoftDeleteAwareTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Health\IllnessRepository")
 * @ORM\Table(name="illness", uniqueConstraints={
 *       @UniqueConstraint(
 *         name="unique_record",
 *         columns={"name"}
 *       )
 *     })
 */
class Illness implements EntityInterface, SoftDeletableEntityInterface
{
    use SoftDeleteAwareTrait;
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
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $information = null;

    /**
     * @ORM\OneToMany(targetEntity="DoctorAppointment", mappedBy="illness")
     */
    private Collection $appointments;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): void
    {
        $this->information = $information;
    }

    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function setAppointments(Collection $appointments): void
    {
        $this->appointments = $appointments;
    }

}