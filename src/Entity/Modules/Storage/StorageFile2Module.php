<?php

namespace App\Entity\Modules\Storage;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Trait\CreateModifyFieldAwareTrait;
use App\Listeners\Entity\EntityStorageFileHydrationSubscriber;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

/**
 * Pseudo polymorphic table (normally not supported by Doctrine).
 * - {@see EntityStorageFileHydrationSubscriber}
 *
 * @ORM\Entity()
 * @ORM\Table(
 *  name="storage_file_2_module",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(
 *            name="unique_record",
 *            columns={"related_module_id", "related_module_class", "storage_file_id"}
 *        )
 *  }
 * )
 */
class StorageFile2Module implements EntityInterface
{
    use CreateModifyFieldAwareTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="related_module_id")
     */
    private int $relatedModuleId;

    /**
     * @ORM\Column(type="string", name="related_module_class", length=255)
     */
    private string $relatedModuleClass;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Storage\StorageFile")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="id", name="storage_file_id")
     */
    private StorageFile $storageFile;

    public function __construct(EntityInterface $moduleEntity, StorageFile $storageFile)
    {
        if (!method_exists($moduleEntity, 'getId')) {
            throw new LogicException("This entity does not have a getId method");
        }

        $this->setCreatedModified();

        $this->relatedModuleId    = $moduleEntity->getId();
        $this->relatedModuleClass = $moduleEntity::class;
        $this->storageFile        = $storageFile;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRelatedModuleId(): int
    {
        return $this->relatedModuleId;
    }

    public function setRelatedModuleId(int $relatedModuleId): void
    {
        $this->relatedModuleId = $relatedModuleId;
    }

    public function getRelatedModuleClass(): string
    {
        return $this->relatedModuleClass;
    }

    public function setRelatedModuleClass(string $relatedModuleClass): void
    {
        $this->relatedModuleClass = $relatedModuleClass;
    }

}
