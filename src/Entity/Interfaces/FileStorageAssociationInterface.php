<?php

namespace App\Entity\Interfaces;

use App\Entity\Modules\Storage\StorageFile;

interface FileStorageAssociationInterface
{
    /**
     * @return StorageFile[]
     */
    public function getStorageFiles(): array;

    /**
     * @param StorageFile[] $storageFiles
     */
    public function setStorageFiles(array $storageFiles): void;

    /**
     * @param StorageFile $storageFile
     */
    public function removeStorageFile(StorageFile $storageFile): void;

    /**
     * @param StorageFile $storageFile
     */
    public function addStorageFile(StorageFile $storageFile): void;
}