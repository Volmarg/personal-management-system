<?php

namespace App\Services;

use App\Controller\Utils\Env;
use App\Controller\Files\FileUploadController;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class FileUploader extends AbstractController {

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory;

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->finder     = new Finder();
        $this->logger     = $logger;

    }

    /**
     * @param UploadedFile $file
     * @param string $type
     * @param string $subdirectory
     * @return Response
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $type, string $subdirectory = '') {

        $this->logger->info("Started uploading files to subdirectory {$subdirectory}");

        $this->handleUploadDir();

        $now                = new \DateTime();
        $originalFilename   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName           = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

        switch($type){
            case FileUploadController::TYPE_FILES:
                $targetDirectory = Env::getFilesUploadDir();
            break;
            case FileUploadController::TYPE_IMAGES:
                $targetDirectory = Env::getImagesUploadDir();
            break;
            default:
                $this->logger->info("Performed upload action for not supported upload type: {$type}");
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
            $this->logger->info("Exception was thrown while uploading files: ", [
                'message' => $e->getMessage()
            ]);
            return new Response('There was an error while uploading files', 500);
        }

        $this->logger->info('Finished uploading data.');
        return new Response('File upload has been successfully finished', 200);
    }

    public function handleUploadDir() {

        $folderCount        = 0;
        $uploadFolderPath   = '';
        $this->finder->directories()->name($this->targetDirectory)->in('.');

        foreach($this->finder as $folder){
            $uploadFolderPath = $folder->getPath();
        }


        try{
            if($folderCount > 0){
                throw new Exception("Found more than one upload folder named {$this->targetDirectory} !");
            }
        }catch(\Exception $e){
            $this->logger->info("Exception was thrown while uploading files: ", [
                'message' => $e->getMessage()
            ]);
        }

        if (!file_exists($uploadFolderPath)) {
            mkdir($this->targetDirectory, 0777);
        }

    }

}