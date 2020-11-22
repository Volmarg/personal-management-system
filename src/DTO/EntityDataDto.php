<?php
namespace App\DTO;

/**
 * Minimal representation of the most important/common used entity information
 *
 * Class EntityDataDto
 * @package App\DTO
 */
class EntityDataDto
{

    /**
     * @var int|null
     */
    private ?int $id;

    /**
     * @var string
     */
    private string $name = "";

    /**
     * @var bool $active
     */
    private bool $active = true;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

}