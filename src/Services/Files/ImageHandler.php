<?php


namespace App\Services\Files;


use App\Controller\Core\Env;
use App\Controller\Files\FilesController;
use Exception;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;


/**
 * Class ImageHandler
 * @package App\Services\Files
 */
class ImageHandler {

    const SKIP_FOR_SIZE_BELOW = 25; //kbytes
    const MINIATURE_MAX_WIDTH = 200;

    const STATUS_MINIATURE_WAS_CREATED        = "MINIATURE_WAS_CREATED";
    const STATUS_MINIATURE_FILE_SIZE_TO_SMALL = "MINIATURE_FILE_SIZE_TO_SMALL";
    const STATUS_SOURCE_FILE_DOES_NOT_EXIST   = "SOURCE_FILE_DOES_NOT_EXIST";

    /**
     * @var string $lastStatus
     */
    private $lastStatus = "";

    /**
     * @return string
     */
    public function getLastStatus() {
        return $this->lastStatus;
    }

    public function __construct() {
        if( !extension_loaded('gd') ){
               throw new Exception("GD extension is not installed (used for images manipulation). Install it for example via: `sudo apt-get install php7.2-gd`!");
        }
    }

    /**
     * Will generate miniature for given image - make sure that file is validated for being image first
     *
     * @param string $filePath - path to the file
     * @param bool $isAbsolutePath - if true then won't try to build absolute path from given path
     * @param string|null $targetMiniatureFileAbsolutePath - if not null then will save output file in this location
     * @throws ImageResizeException
     * @throws Exception
     */
    public function createMiniature(string $filePath, bool $isAbsolutePath = false, ?string $targetMiniatureFileAbsolutePath = null): void
    {
        $filesize = filesize($filePath)/1024;

        if( $filesize <= self::SKIP_FOR_SIZE_BELOW ){
            $this->lastStatus = self::STATUS_MINIATURE_FILE_SIZE_TO_SMALL;
            return;
        }

        $cwd = getcwd();

        $absoluteFilePath = $filePath;
        if( !$isAbsolutePath ){
            $absoluteFilePath  = $cwd . DIRECTORY_SEPARATOR . $filePath;
        }

        if( is_null($targetMiniatureFileAbsolutePath) ){
            $targetDirectory = $this->generateMiniatureAbsoluteDirectoryPathForOriginalPath($filePath);
            $targetFile      = $this->generateMiniatureAbsolutePathForOriginalPath($filePath);
        }else{
            $targetDirectory = pathinfo($targetMiniatureFileAbsolutePath, PATHINFO_DIRNAME);
            $targetFile      = $targetMiniatureFileAbsolutePath;
        }

        if( file_exists($targetFile) ){
            $this->lastStatus = self::STATUS_SOURCE_FILE_DOES_NOT_EXIST;
            return;
        }

        if( !file_exists($targetDirectory) ){
            mkdir($targetDirectory, 0755, true);
        }

        $image = new ImageResize($absoluteFilePath);
        $image->resizeToHeight(self::MINIATURE_MAX_WIDTH);
        $image->save($targetFile);

        $this->lastStatus = self::STATUS_MINIATURE_WAS_CREATED;
    }

    /**
     * Will generate miniature target file path from give source file path
     *
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    public function generateMiniatureAbsolutePathForOriginalPath(string $filePath): string
    {
        if( "cli" === php_sapi_name() ){
            throw new Exception("This function should not be called from CLI as it's root directory differs");
        }

        $imageName      = pathinfo($filePath, PATHINFO_FILENAME);
        $imageExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        $targetDirectory = $this->generateMiniatureAbsoluteDirectoryPathForOriginalPath($filePath);
        $targetFile      = $targetDirectory . DIRECTORY_SEPARATOR . $imageName . DOT . $imageExtension;

        return $targetFile;
    }

    /**
     * Will generate target directory for miniature file based on the source file path
     *
     * @param string $filePath
     * @return string
     */
    private function generateMiniatureAbsoluteDirectoryPathForOriginalPath(string $filePath): string
    {
        $cwd             = getcwd();
        $imagePath       = FilesController::stripUploadDirectoryFromFilePathFront(pathinfo($filePath, PATHINFO_DIRNAME));
        $targetDirectory = $cwd . DIRECTORY_SEPARATOR . Env::getMiniaturesUploadDir() . DIRECTORY_SEPARATOR . $imagePath;

        return $targetDirectory;
    }

}