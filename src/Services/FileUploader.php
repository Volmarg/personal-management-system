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

        if( Env::isDemo() ){
            $is_file_valid = $this->isFileValid($file, $request);

            if( !$is_file_valid ){
                return new Response('File is invalid, and has been skipped', 500);
            }
        }

        $this->handleUploadDir();

        $now                = new \DateTime();
        $original_filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $file_name          = $original_filename . '-' . uniqid() . '.' . $file->guessExtension();

        switch($type){
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $target_directory = Env::getFilesUploadDir();
            break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $target_directory = Env::getImagesUploadDir();
            break;
            default:
                $this->logger->info("Performed upload action for not supported upload type: {$type}");
                throw new \Exception('This type is not allowed');
        }

        if (file_exists($target_directory . '/' . $file_name)) {
            $file_name .= '_' . $now->format('Y_m_d');
        }

        # check if the target folder is main folder
        if ( !empty($subdirectory) && $subdirectory !== $target_directory ) {
            $target_directory .= '/' . $subdirectory;
        }

        try {
            $file->move($target_directory, $file_name);
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

        $folder_count        = 0;
        $upload_folder_path  = '';
        $this->finder->directories()->name($this->target_directory)->in('.');

        foreach($this->finder as $folder){
            $upload_folder_path = $folder->getPath();
        }


        try{
            if($folder_count > 0){
                throw new Exception("Found more than one upload folder named {$this->target_directory} !");
            }
        }catch(\Exception $e){
            $this->logger->info("Exception was thrown while uploading files: ", [
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