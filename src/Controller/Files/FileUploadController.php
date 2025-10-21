<?php


namespace App\Controller\Files;

use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Services\Files\DirectoriesHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileUploadController extends AbstractController {

    const MODULE_UPLOAD_DIR_FOR_IMAGES  = 'images';
    const MODULE_UPLOAD_DIR_FOR_VIDEO   = 'videos';
    const MODULE_UPLOAD_DIR_FOR_FILES   = 'files';

    // info: might cause issue upon creating subdirectory named `upload`
    const REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH         = "[\/]?upload\/(?<" . self::REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH_DIRNAME . ">[a-zA-z]+)\/";
    const REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH_DIRNAME = "DIR_NAME";

    const MODULE_UPLOAD_DIR_TO_MODULE_NAME = [
       self::MODULE_UPLOAD_DIR_FOR_IMAGES => ModulesController::MODULE_NAME_IMAGES,
       self::MODULE_UPLOAD_DIR_FOR_FILES  => ModulesController::MODULE_NAME_FILES,
       self::MODULE_UPLOAD_DIR_FOR_VIDEO  => ModulesController::MODULE_NAME_VIDEO,
    ];

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private $directoriesHandler;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(DirectoriesHandler $directoriesHandler, LockedResourceController $lockedResourceController) {
        $this->directoriesHandler       = $directoriesHandler;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * @param string $uploadModuleDir
     * @return mixed
     * @throws Exception
     */
    public static function getTargetDirectoryForUploadModuleDir(string $uploadModuleDir){
        switch ($uploadModuleDir) {
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
                throw new Exception("This upload module is not supported: {$uploadModuleDir}");
        }

        return $targetDirectory;
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectoryName
     * @return bool
     */
    public static function isSubdirectoryForModuleDirExisting(string $targetDirectory, string $subdirectoryName): bool {
        $subdirectoryPath = static::getSubdirectoryPath($targetDirectory, $subdirectoryName);
        return file_exists($subdirectoryPath);
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectoryName
     * @return string
     */
    public static function getSubdirectoryPath(string $targetDirectory, string $subdirectoryName){
        return $targetDirectory . '/' . $subdirectoryName;
    }

    /**
     * Will return the upload module name for file path
     *
     * @param string $filepath
     * @return string
     * @throws Exception
     */
    public static function getUploadModuleNameForFilePath(string $filepath): string
    {
        preg_match("#" . self::REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH . "#", $filepath, $matches);
        $uploadModuleDir = $matches[self::REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH_DIRNAME];

        if( !array_key_exists($uploadModuleDir,FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME) ){
            $message = "Given upload_module_dir is not an upload module dir";
            throw new Exception($message);
        }

        $moduleName = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$uploadModuleDir];

        return $moduleName;
    }

}