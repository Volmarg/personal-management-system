<?php

namespace App\Entity\Modules\Car;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Car\MyCarSchedulesTypesRepository")
 */
class MyCarSchedulesTypes
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
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Car\MyCar", mappedBy="scheduleType")
     */
    private $myCars;

    public function __construct()
    {
        $this->myCars = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
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

    /**
     * @return Collection|MyCar[]
     */
    public function getMyCars(): Collection
    {
        return $this->myCars;
    }

    public function addMyCar(MyCar $myCar): self
    {
        if (!$this->myCars->contains($myCar)) {
            $this->myCars[] = $myCar;
            $myCar->setScheduleType($this);
        }

        return $this;
    }

    public function removeMyCar(MyCar $myCar): self
    {
        if ($this->myCars->contains($myCar)) {
            $this->myCars->removeElement($myCar);
            // set the owning side to null (unless already changed)
            if ($myCar->getScheduleType() === $this) {
                $myCar->setScheduleType(null);
            }
        }

        return $this;
    }
}
