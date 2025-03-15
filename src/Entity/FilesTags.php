<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;
use LogicException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilesTagsRepository")
 * @Table(name="files_tags",indexes={@Index(name="file_path_index", columns={"full_file_path"})})
 */
class FilesTags implements SoftDeletableEntityInterface, EntityInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullFilePath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tags;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getFullFilePath(): ?string
    {
        return $this->fullFilePath;
    }

    public function setFullFilePath(string $fullFilePath): self
    {
        $this->fullFilePath = $fullFilePath;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param array $checkedValues
     *
     * @return bool
     */
    public function isAnyTagMatching(array $checkedValues): bool
    {
        $tagsString = $this->getTags() ?? "[]";
        $tags       = json_decode($tagsString);
        if (empty($tags)) {
            return false;
        }

        foreach ($checkedValues as $value) {
            if (!is_string($value)) {
                throw new LogicException("At least one of the array elements is not a string");
            }

            if (in_array($value, $tags)) {
                return true;
            }
        }

        return false;
    }
}
