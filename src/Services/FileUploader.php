<?php

namespace App\Services;

use App\Controller\Utils\Env;
use App\Controller\FileUploadController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader extends AbstractController {

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory;

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder     = new Finder();
    }

    /**
     * @param UploadedFile $file
     * @param string $type
     * @param string $subdirectory
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $type, string $subdirectory = '') {

        $this->handleUploadDir();

        $now                = new \DateTime();
        $originalFilename   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName           = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

        switch($type){
            case FileUploadController::TYPE_FILE:
                $targetDirectory = Env::getFilesUploadDir();
            break;
            case FileUploadController::TYPE_IMAGE:
                $targetDirectory = Env::getImagesUploadDir();
            break;
            default:
                throw new \Exception('This type is not allowed');
        }

        if (file_exists($targetDirectory . '/' . $fileName)) {
            $fileName .= '_' . $now->format('Y_m_d');
        }

        if (!empty($subdirectory)) {
            $targetDirectory .= '/' . $subdirectory;
        }

        try {
            $file->move($targetDirectory, $fileName);
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