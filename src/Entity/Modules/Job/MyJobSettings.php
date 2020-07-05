<?php

namespace App\Entity\Modules\Job;

use App\Entity\Interfaces\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Job\MyJobSettingsRepository")
 * @ORM\Table(name="my_job_setting")
 */
class MyJobSettings implements EntityInterface{
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
    private $value;

    public function getId(): ?int {
        return $this->id;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(string $type): self {
        $this->type = $type;

        return $this;
    }

    public function getValue(): ?string {
        return $this->value;
    }

    public function setValue(string $value): self {
        $this->value = $value;

        return $this;
    }
}
