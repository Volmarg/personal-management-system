<?php

namespace App\Entity\Trait;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait CreateModifyFieldAwareTrait
{
    /**
     * @ORM\Column(type="datetime", name="created", columnDefinition="DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL")
     */
    private DateTime $created;

    /**
     * @ORM\Column(type="datetime", name="modified", columnDefinition="DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL")
     */
    private DateTime $modified;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getModified(): DateTime
    {
        return $this->modified;
    }

    public function setModified(DateTime $modified): void
    {
        $this->modified = $modified;
    }

    public function setCreatedModified(?DateTime $created = null, ?DateTime $modified = null): void
    {
        $this->modified = $created ?? new DateTime();
        $this->created = $modified ?? new DateTime();
    }

}