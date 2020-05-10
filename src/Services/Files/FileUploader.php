<?php

namespace App\Services\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Services\Exceptions;
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
     * @var string $target_directory
     */
    private $target_directory;

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
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    public function __construct(LoggerInterface $logger, Application $app, FilesTagsController $files_tags_controller) {
        $this->files_tags_controller = $files_tags_controller;
        $this->finder                = new Finder();
        $this->logger                = $logger;
        $this->app                   = $app;
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
            $is_file_valid = $this->isFileValid($file, $request);

            if( !$is_file_valid ){
                $message = $this->app->translator->translate('responses.upload.invalidFileHasBeenSkipped') . $subdirectory;
                return new Response($message, 500);
            }
        }

        $this->handleUploadDir();

        $now = new \DateTime();

        switch($type){
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $target_directory = Env::getFilesUploadDir();
            break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $target_directory = Env::getImagesUploadDir();
            break;
            default:
                $log_message = $this->app->translator->translate('logs.upload.triedToUploadForUnknownUploadType') . $type;
                $exc_message = $this->app->translator->translate('exception.upload.thisUploadTypeIsNotAllowed') . $type;
                $this->logger->info($log_message);
                throw new \Exception($exc_message);
        }

        $original_filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $file_extension     = $file->guessExtension();

        $extension = $extension ?: $file_extension;
        $filename  = $filename  ?: $original_filename;

        # check if the target folder is main folder
        if ( !empty($subdirectory) && $subdirectory !== $target_directory ) {
            $target_directory .= DIRECTORY_SEPARATOR . $subdirectory;
        }

        $file_full_path = $target_directory . DIRECTORY_SEPARATOR . $filename . DOT . $extension;
        if (file_exists($file_full_path)) {
            $filename .= '_' . $now->format('Y_m_d_H_i_s_u');
            $file_full_path = $target_directory . DIRECTORY_SEPARATOR . $filename . DOT . $extension;
        }

        $filename_with_extension = $filename . DOT . $extension;

        try {
            $file->move($target_directory, $filename_with_extension);

            if( !empty($tags) ){
                $this->files_tags_controller->updateTags($tags, $file_full_path);
            }

        } catch (FileException $e) {
            $message = $this->app->translator->translate('upload.errors.thereWasAnErrorWhileUploadingFiles');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);
            return new Response($message, 500);
        }

        $log_message       = $this->app->translator->translate('logs.upload.finishedUploading');
        $response_message  = $this->app->translator->translate('responses.upload.finishedUploading');
        $this->logger->info($log_message);
        return new Response($response_message, 200);
    }

    public function handleUploadDir() {

        $folder_count        = 0;
        $upload_folder_path  = '';
        $this->finder->directories()->name($this->target_directory)->in('.');

        foreach($this->finder as $folder){
            $upload_folder_path = $folder->getPath();
        }


        try{
            if($folder_count > 0){
                $message = $this->app->translator->translate('exceptions.upload.foundMoreThanOneDirWithName') . $this->target_directory;
                throw new Exception($message);
            }
        }catch(\Exception $e){
            $message = $this->app->translator->translate('upload.errors.thereWasAnErrorWhileUploadingFiles');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);
        }

        if (!file_exists($upload_folder_path)) {
            mkdir($this->target_directory, 0777);
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

        $is_mime_allowed      = $this->isMimeAllowed($mime);
        $is_extension_allowed = $this->isExtensionAllowed($extension);
        $is_file_name_allowed = $this->isFileNameAllowed($filename);

        if(!$is_mime_allowed || !$is_extension_allowed || !$is_file_name_allowed){
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