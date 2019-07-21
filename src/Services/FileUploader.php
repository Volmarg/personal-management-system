<?php

namespace App\Services;

use App\Controller\EnvController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader {

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory;

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct(Finder $finder) {
        $this->targetDirectory = EnvController::getUploadDir();
        $this->finder          = $finder;
    }

    public function upload(UploadedFile $file) {

        $this->handleUploadDir();

        $originalFilename   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename       = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $fileName           = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {

        }

    }

    public function handleUploadDir() {

        $folderCount        = 0;
        $uploadFolderPath   = '';
        $this->finder->directories()->name($this->targetDirectory)->in('.');

        foreach($this->finder as $folder){
            $uploadFolderPath = $folder->getPath();
        }

        if($folderCount > 0){
            throw new Exception("Found more than one upload folder named {$this->targetDirectory} !");
        }

        if (!file_exists($uploadFolderPath)) {
            mkdir($this->targetDirectory, 0777);
        }

    }

}