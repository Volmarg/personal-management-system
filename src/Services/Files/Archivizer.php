<?php

namespace App\Services\Files;

class Archivizer {

    /**
     * @var string
     */
    private $zipping_status;

    /**
     * @var string
     */
    private $archive_name;

    /**
     * @var string
     */
    private $directory_to_zip;

    /**
     * @var bool
     */
    private $zip_recursively = true;

    /**
     * @var bool
     */
    private $isZippedSuccessfully = false;

    /**
     * @var ZipArchive $zip
     */
    private $zip;

    /**
     * @return string
     */
    public function getZippingStatus(): string {
        return $this->zipping_status;
    }

    /**
     * @param string $zipping_status
     */
    public function setZippingStatus(string $zipping_status): void {
        $this->zipping_status = $zipping_status;
    }

    /**
     * @return string
     */
    public function getArchiveName(): string {
        return $this->archive_name;
    }

    /**
     * @param string $archive_name
     */
    public function setArchiveName(string $archive_name): void {
        $this->archive_name = $archive_name;
    }

    /**
     * @return string
     */
    public function getDirectoryToZip(): string {
        return $this->directory_to_zip;
    }

    /**
     * @param string $directory_to_zip
     */
    public function setDirectoryToZip(string $directory_to_zip): void {
        $this->directory_to_zip = $directory_to_zip;
    }

    /**
     * @return bool
     */
    public function isZipRecursively(): bool {
        return $this->zip_recursively;
    }

    /**
     * @param bool $zip_recursively
     */
    public function setZipRecursively(bool $zip_recursively): void {
        $this->zip_recursively = $zip_recursively;
    }

    /**
     * @return bool
     */
    public function isZippedSuccessfully(): bool {
        return $this->isZippedSuccessfully;
    }

    /**
     * @param bool $isZippedSuccessfully
     */
    public function setIsZippedSuccessfully(bool $isZippedSuccessfully): void {
        $this->isZippedSuccessfully = $isZippedSuccessfully;
    }

    /**
     * DatabaseExporter constructor.
     * @param ZipArchive $zip_archive
     */
    public function __construct(ZipArchive $zip_archive) {
        $this->zip = $zip_archive;
    }



}