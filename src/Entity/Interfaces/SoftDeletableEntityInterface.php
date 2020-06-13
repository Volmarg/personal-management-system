<?php


namespace App\Entity\Interfaces;


interface SoftDeletableEntityInterface
{
    /**
     * @return bool|null
     */
    public function isDeleted():?bool;

    /**
     * @param bool $deleted
     * @return SoftDeletableEntityInterface
     */
    public function setDeleted(bool $deleted);
}