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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileUploader extends AbstractController {

    const EXCLUDED_MIMES = [
        'text/css',
        'application/octet-stream',
        'text/html',
        'application/java-archive',
        'text/javascript',
        'application/json',
        'application/ld+json',
        'text/javascript',
        'application/x-rar-compressed',
        'application/zip',
        'application/x-7z-compressed'
    ];

    const EXCLUDED_FILES_EXTENSIONS = [
        'exe', 'php', 'sh', '.js', 'cc',
        'zip', 'rar', 'css', 'bin', 'htm',
        'html', 'jar', 'mjs', '7z',
        'json', 'jsonld',
    ];

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
     * @param Request $request
     * @param string $type
     * @param string $subdirectory
     * @return Response
     * @throws \Exception
     */
    public function upload(UploadedFile $file, Request $request, string $type, string $subdirectory = '') {

        $this->logger->info("Started uploading files to subdirectory {$subdirectory}");
        $isFileValid = $this->isFileValid($file, $request);

        if( !$isFileValid ){
            return new Response('File is invalid, and has been skipped', 500);
        }

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

    /**
     * This is used only for demo instance, to skip files and eventually ban some IP
     * @param UploadedFile $file
     * @param Request $request
     * @return bool
     */
    private function isFileValid(UploadedFile $file, Request $request){

        $filename   = $file->getFilename();
        $extension  = $file->getExtension();
        $mime       = $file->getMimeType();

        $isMimeAllowed      = $this->isMimeAllowed($mime);
        $isExtensionAllowed = $this->isExtensionAllowed($extension);
        $isFileNameAllowed  = $this->isFileNameAllowed($filename);

        if(!$isMimeAllowed || !$isExtensionAllowed || !$isFileNameAllowed){
            $this->logger->info("Skipped file.", [
                'filename'      =>  $filename,
                'extension'     =>  $extension,
                'mime'          =>  $mime,
                'requestIp'     =>  $request->getClientIp(),
                'remoteAddr'    =>  $_SERVER['REMOTE_ADDR'],
            ]);

            return false;
        }

        return true;
    }

    private function isExtensionAllowed($extension) {
        return ( !in_array($extension, static::EXCLUDED_FILES_EXTENSIONS) );
    }

    private function isFileNameAllowed($filename) {

        if( preg_match("([^\w\s\d\-_~,;\[\]\(\).])", $filename) ){
            return false;
        }elseif( preg_match("([\.]{2,})", $filename) ){
            return false;
        }

        return true;
    }

    private function isMimeAllowed($mime) {
        return ( !in_array($mime, static::EXCLUDED_MIMES) );
    }

}