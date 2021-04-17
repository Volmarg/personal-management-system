<?php

namespace App\Services\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Services\Validation\FileValidatorService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
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

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FilesTagsController $filesTagsController
     */
    private $filesTagsController;

    /**
     * @var ImageHandler $imageHandler
     */
    private $imageHandler;

    /**
     * @var FileValidatorService $fileValidator
     */
    private FileValidatorService $fileValidator;

    public function __construct(
        LoggerInterface     $logger,
        Application         $app,
        FilesTagsController $filesTagsController,
        ImageHandler        $imageHandler,
        FileValidatorService       $fileValidator
    ) {
        $this->filesTagsController = $filesTagsController;
        $this->fileValidator       = $fileValidator;
        $this->imageHandler        = $imageHandler;
        $this->finder              = new Finder();
        $this->logger              = $logger;
        $this->app                 = $app;
    }

    /**
     * @param UploadedFile $file
     * @param Request $request
     * @param string $type
     * @param string $subdirectory
     * @param string $filename
     * @param string $extension
     * @param string $tags
     * @return Response
     * @throws \Exception
     */
    public function upload(UploadedFile $file, Request $request, string $type, string $subdirectory = '', string $filename = '', string $extension = '', string $tags = '') {

        $message = $this->app->translator->translate('logs.upload.startedUploadingToSubdirectory') . $subdirectory;
        $this->logger->info($message);

        if( Env::isDemo() ){
            $isFileValid = $this->isFileValid($file, $request);

            if( !$isFileValid ){
                $message = $this->app->translator->translate('responses.upload.invalidFileHasBeenSkipped') . $subdirectory;
                return new Response($message, 500);
            }
        }

        $this->handleUploadDir();

        $now = new \DateTime();

        switch($type){
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $targetDirectory = Env::getFilesUploadDir();
            break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $targetDirectory = Env::getImagesUploadDir();
            break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_VIDEO:
                $targetDirectory = Env::getVideoUploadDir();
                break;
            default:
                $logMessage = $this->app->translator->translate('logs.upload.triedToUploadForUnknownUploadType') . $type;
                $excMessage = $this->app->translator->translate('exception.upload.thisUploadTypeIsNotAllowed') . $type;
                $this->logger->info($logMessage);
                throw new \Exception($excMessage);
        }

        $originalFilename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileExtension     = $file->guessExtension();

        $extension = $extension ?: $fileExtension;
        $filename  = $filename  ?: $originalFilename;

        # check if the target folder is main folder
        if ( !empty($subdirectory) && $subdirectory !== $targetDirectory ) {
            $targetDirectory .= DIRECTORY_SEPARATOR . $subdirectory;
        }

        $fileFullPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename . DOT . $extension;
        if (file_exists($fileFullPath)) {
            $filename .= '_' . $now->format('Y_m_d_H_i_s_u');
            $fileFullPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename . DOT . $extension;
        }

        $filenameWithExtension = $filename . DOT . $extension;

        try {
            $file->move($targetDirectory, $filenameWithExtension);

            // Todo(maybe) in future: make checking which file based module is targeted - generate miniatures only for MyImages, as it will be only used in this case
            $movedFile = new File($fileFullPath);
            if( $this->fileValidator->isFileImage($movedFile) && $this->fileValidator->isImageResizable($movedFile)){
                $this->imageHandler->createMiniature($fileFullPath);
            }

            if( !empty($tags) ){
                $this->filesTagsController->updateTags($tags, $fileFullPath);
            }

        } catch (FileException $e) {
            $message = $this->app->translator->translate('upload.errors.thereWasAnErrorWhileUploadingFiles');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);
            return new Response($message, 500);
        }

        $logMessage       = $this->app->translator->translate('logs.upload.finishedUploading');
        $responseMessage  = $this->app->translator->translate('responses.upload.finishedUploading');
        $this->logger->info($logMessage);
        return new Response($responseMessage, 200);
    }

    public function handleUploadDir() {

        $folderCount       = 0;
        $uploadFolderPath  = '';
        $this->finder->directories()->name($this->targetDirectory)->in('.');

        foreach($this->finder as $folder){
            $uploadFolderPath = $folder->getPath();
        }


        try{
            if($folderCount > 0){
                $message = $this->app->translator->translate('exceptions.upload.foundMoreThanOneDirWithName') . $this->targetDirectory;
                throw new Exception($message);
            }
        }catch(\Exception $e){
            $message = $this->app->translator->translate('upload.errors.thereWasAnErrorWhileUploadingFiles');
            $this->logger->info($message, [
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

        $filename   = $file->getClientOriginalName();
        $extension  = $file->getClientOriginalExtension();
        $mime       = $file->getClientMimeType();

        $isMimeAllowed      = $this->isMimeAllowed($mime);
        $isExtensionAllowed = $this->isExtensionAllowed($extension);
        $isFileNameAllowed  = $this->isFileNameAllowed($filename);

        if(!$isMimeAllowed || !$isExtensionAllowed || !$isFileNameAllowed){
            $this->logger->critical("Skipped file.", [
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