<?php


namespace App\Services\Files;


use App\Controller\Core\Env;
use App\Controller\Files\FilesController;
use App\Controller\Utils\Utils;
use Exception;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;


/**
 * Class ImageHandler
 * @package App\Services\Files
 */
class ImageHandler {

    const KEY_MINIATURE_PATH  = 'miniature_path';
    const SKIP_FOR_SIZE_BELOW = 25; //kbytes
    const MINIATURE_MAX_WIDTH = 200;

    const STATUS_MINIATURE_WAS_CREATED        = "MINIATURE_WAS_CREATED";
    const STATUS_MINIATURE_FILE_SIZE_TO_SMALL = "MINIATURE_FILE_SIZE_TO_SMALL";
    const STATUS_SOURCE_FILE_DOES_NOT_EXIST   = "SOURCE_FILE_DOES_NOT_EXIST";

    /**
     * @var string $last_status
     */
    private $last_status = "";

    /**
     * @return string
     */
    public function getLastStatus() {
        return $this->last_status;
    }

    public function __construct() {
        if( !extension_loaded('gd') ){
               throw new Exception("GD extension is not installed (used for images manipulation). Install it for example via: `sudo apt-get install php7.2-gd`!");
        }
    }

    /**
     * Will generate miniature for given image - make sure that file is validated for being image first
     *
     * @param string $file_path - path to the file
     * @param bool $is_absolute_path - if true then won't try to build absolute path from given path
     * @param string|null $target_miniature_file_absolute_path - if not null then will save output file in this location
     * @throws ImageResizeException
     * @throws Exception
     */
    public function createMiniature(string $file_path, bool $is_absolute_path = false, ?string $target_miniature_file_absolute_path = null): void
    {
        $filesize = filesize($file_path)/1024;

        if( $filesize <= self::SKIP_FOR_SIZE_BELOW ){
            $this->last_status = self::STATUS_MINIATURE_FILE_SIZE_TO_SMALL;
            return;
        }

        $cwd = getcwd();

        $absolute_file_path = $file_path;
        if( !$is_absolute_path ){
            $absolute_file_path  = $cwd . DIRECTORY_SEPARATOR . $file_path;
        }

        if( is_null($target_miniature_file_absolute_path) ){
            $target_directory = $this->generateMiniatureAbsoluteDirectoryPathForOriginalPath($file_path);
            $target_file      = $this->generateMiniatureAbsolutePathForOriginalPath($file_path);
        }else{
            $target_directory = pathinfo($target_miniature_file_absolute_path, PATHINFO_DIRNAME);
            $target_file      = $target_miniature_file_absolute_path;
        }

        if( file_exists($target_file) ){
            $this->last_status = self::STATUS_SOURCE_FILE_DOES_NOT_EXIST;
            return;
        }

        if( !file_exists($target_directory) ){
            mkdir($target_directory, 0755, true);
        }

        $image = new ImageResize($absolute_file_path);
        $image->resizeToHeight(self::MINIATURE_MAX_WIDTH);
        $image->save($target_file);

        $this->last_status = self::STATUS_MINIATURE_WAS_CREATED;
    }

    /**
     * Will move miniature to the target directory (if no such exist then it will be created
     * Otherwise if miniature does not exist it will be directly created in target directory (also will be created if does not exist)
     *
     * Issue: empty folders remains (are not removed)
     *
     * @param string $current_file_location
     * @param string $target_file_location
     * @throws Exception
     */
    public function moveMiniatureBasedOnMovingOriginalFile(string $current_file_location, string $target_file_location): void
    {
        $target_miniature_file_for_current_location = $this->generateMiniatureAbsolutePathForOriginalPath($current_file_location);
        $target_miniature_file_for_target_location  = $this->generateMiniatureAbsolutePathForOriginalPath($target_file_location);
        $target_miniature_directory                 = $this->generateMiniatureAbsoluteDirectoryPathForOriginalPath($target_file_location);

        if( !file_exists($target_miniature_directory) ){
            mkdir($target_miniature_directory, 0755, true);
        }

        if( file_exists($target_miniature_file_for_current_location) ){
            Utils::copyFiles($target_miniature_file_for_current_location, $target_miniature_file_for_target_location);
            unlink($target_miniature_file_for_current_location);
        }else{
            $this->createMiniature($target_file_location);
        }
    }

    /**
     * Will generate miniature target file path from give source file path
     *
     * @param string $file_path
     * @return string
     * @throws Exception
     */
    public function generateMiniatureAbsolutePathForOriginalPath(string $file_path): string
    {
        if( "cli" === php_sapi_name() ){
            throw new Exception("This function should not be called from CLI as it's root directory differs");
        }

        $image_name      = pathinfo($file_path, PATHINFO_FILENAME);
        $image_extension = pathinfo($file_path, PATHINFO_EXTENSION);

        $target_directory = $this->generateMiniatureAbsoluteDirectoryPathForOriginalPath($file_path);
        $target_file      = $target_directory . DIRECTORY_SEPARATOR . $image_name . DOT . $image_extension;

        return $target_file;
    }

    /**
     * Will generate target directory for miniature file based on the source file path
     *
     * @param string $file_path
     * @return string
     */
    private function generateMiniatureAbsoluteDirectoryPathForOriginalPath(string $file_path): string
    {
        $cwd              = getcwd();
        $image_path       = FilesController::stripUploadDirectoryFromFilePathFront(pathinfo($file_path, PATHINFO_DIRNAME));
        $target_directory = $cwd . DIRECTORY_SEPARATOR . Env::getMiniaturesUploadDir() . DIRECTORY_SEPARATOR . $image_path;

        return $target_directory;
    }

}