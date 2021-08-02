<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Entity\System\LockedResource;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileUploadController extends AbstractController {

    const MODULE_UPLOAD_DIR_FOR_IMAGES  = 'images';
    const MODULE_UPLOAD_DIR_FOR_VIDEO   = 'videos';
    const MODULE_UPLOAD_DIR_FOR_FILES   = 'files';

    const KEY_SUBDIRECTORY_NEW_NAME       = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME   = 'subdirectory_current_name';

    const KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR   = 'subdirectory_current_path_in_module_upload_dir';
    const KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR    = 'subdirectory_target_path_in_module_upload_dir';

    const KEY_SUBDIRECTORY_NAME         = 'subdirectory_name';

    const KEY_UPLOAD_MODULE_DIR         = 'upload_module_dir';

    const KEY_MAIN_FOLDER               = 'Main folder';

    const KEY_TAG           = 'tag';
    const KEY_FILENAME      = 'fileName';
    const KEY_EXTENSION     = 'fileExtension';
    const KEY_UPLOAD_TABLE  = 'upload_table';

    // info: might cause issue upon creating subdirectory named `upload`
    const REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH         = "[\/]?upload\/(?<" . self::REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH_DIRNAME . ">[a-zA-z]+)\/";
    const REGEX_MATCH_UPLOAD_MODULE_DIR_FOR_FILE_PATH_DIRNAME = "DIR_NAME";

    const MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES = [
        MyImagesController::MODULE_NAME       => self::MODULE_UPLOAD_DIR_FOR_IMAGES,
        MyFilesController::MODULE_NAME        => self::MODULE_UPLOAD_DIR_FOR_FILES,
        ModulesController::MODULE_NAME_VIDEO  => self::MODULE_UPLOAD_DIR_FOR_VIDEO,
    ];

    const MODULE_UPLOAD_DIR_TO_MODULE_NAME = [
       self::MODULE_UPLOAD_DIR_FOR_IMAGES => ModulesController::MODULE_NAME_IMAGES,
       self::MODULE_UPLOAD_DIR_FOR_FILES  => MyFilesController::MODULE_NAME,
       self::MODULE_UPLOAD_DIR_FOR_VIDEO  => ModulesController::MODULE_NAME_VIDEO,
    ];

    /**
     * @var FilesHandler $filesHandler
     */
    private $filesHandler;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private $directoriesHandler;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(FilesHandler $filesHandler, DirectoriesHandler $directoriesHandler, Application $app, LockedResourceController $lockedResourceController) {
        $this->app                      = $app;
        $this->filesHandler             = $filesHandler;
        $this->directoriesHandler       = $directoriesHandler;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * @param string $uploadModuleDir
     * @return mixed
     * @throws Exception
     */
    public static function getTargetDirectoryForUploadModuleDir(string $uploadModuleDir){
        $translator = new Translator();

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
                $message  = $translator->translate('responses.upload.uploadDirNotSupported');
                throw new Exception($message);
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

    /**
     * Will return upload dirs ony for unlocked modules
     *
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUploadModulesDirsForNonLockedModule(): array
    {
        $uploadDirsForModulesNames = [];
        foreach(self::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES as $moduleName => $uploadDir){
            if( $this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $moduleName, false) ){
                $uploadDirsForModulesNames[$moduleName] = $uploadDir;
            }
        }

        return $uploadDirsForModulesNames;
    }

}