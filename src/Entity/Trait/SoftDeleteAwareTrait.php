<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteAwareTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted = false;

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

}