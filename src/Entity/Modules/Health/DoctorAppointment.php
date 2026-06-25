<?php

namespace App\Entity\Modules\Health;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\FileStorageAssociationInterface;
use App\Entity\Trait\CreateModifyFieldAwareTrait;
use App\Entity\Trait\FileStorageAssociationTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Health\DoctorAppointmentRepository")
 * @ORM\Table(name="doctor_appointment")
 */
class DoctorAppointment implements FileStorageAssociationInterface, EntityInterface
{
    use FileStorageAssociationTrait;
    use CreateModifyFieldAwareTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private DateTime $date;

    /**
     * @ORM\ManyToOne(targetEntity="Doctor", inversedBy="appointments")
     * @ORM\JoinColumn(name="doctor_id", referencedColumnName="id")
     */
    private Doctor $doctor;

    /**
     * @ORM\ManyToOne(targetEntity="Illness", inversedBy="appointments")
     * @ORM\JoinColumn(name="illness_id", referencedColumnName="id")
     */
    private Illness $illness;

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

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function getIllness(): Illness
    {
        return $this->illness;
    }

    public function setIllness(Illness $illness): void
    {
        $this->illness = $illness;
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