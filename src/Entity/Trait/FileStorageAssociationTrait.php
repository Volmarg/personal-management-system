<?php

namespace App\Entity\Trait;

use App\Entity\Modules\Storage\StorageFile;

trait FileStorageAssociationTrait
{
    /**
     * @var StorageFile[]
     */
    private array $storageFiles = [];

    /**
     * @return StorageFile[]
     */
    public function getStorageFiles(): array
    {
        return $this->storageFiles;
    }

    /**
     * @param StorageFile[] $storageFiles
     */
    public function setStorageFiles(array $storageFiles): void
    {
        $this->storageFiles = $storageFiles;
    }

    /**
     * @param StorageFile $storageFile
     */
    public function addStorageFile(StorageFile $storageFile): void
    {
        foreach ($this->storageFiles as $file) {
            if ($file->getId() === $storageFile->getId()) {
                return;
            }
        }

        $this->storageFiles[] = $storageFile;
    }

    /**
     * @param StorageFile[] $storageFiles
     */
    public function removeStorageFiles(array $storageFiles): void
    {
        foreach ($storageFiles as $storageFile) {
            foreach ($this->storageFiles as $index => $file) {
                if ($file->getId() === $storageFile->getId()) {
                    unset($this->storageFiles[$index]);
                    continue 2;
                }
            }
        }
    }

}