<?php

namespace App\Services\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Controller\Modules\ModuleDataController;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Controller\Utils\Utils;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use DirectoryIterator;
use Doctrine\DBAL\Driver\Exception as DbalException;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This service is responsible for handling folders in terms of internal usage, like moving/renaming/etc...
 * Class DirectoriesHandler
 * @package App\Services
 */
class DirectoriesHandler {

    const SUBDIRECTORY_KEY  = 'subdirectory';
    const KEY_BLOCK_REMOVAL = 'block_removal';

    /**
     * @var Application $application
     */
    private $application;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var FileTagger $fileTagger
     */
    private $fileTagger;

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FilesTagsController $filesTagsController
     */
    private $filesTagsController;

    /**
     * Info: must remain static due to the static methods requiring this logic
     * @var LockedResourceController $lockedResourceController
     */
    private static LockedResourceController $lockedResourceController;

    /**
     * @var ModuleDataController $moduleDataController
     */
    private ModuleDataController $moduleDataController;

    public function __construct(
        Application              $application,
        LoggerInterface          $logger,
        FileTagger               $fileTagger,
        FilesTagsController      $filesTagsController,
        LockedResourceController $lockedResourceController,
        ModuleDataController     $moduleDataController
    ) {
        self::$lockedResourceController = $lockedResourceController;
        $this->application              = $application;
        $this->logger                   = $logger;
        $this->finder                   = new Finder();
        $this->fileTagger               = $fileTagger;
        $this->filesTagsController      = $filesTagsController;
        $this->moduleDataController     = $moduleDataController;
    }

    /**
     * @param string|null $uploadModuleDir
     * @param string|null $currentDirectoryPathInModuleUploadDir
     * @param bool $blocksRemoval ( will prevent removing folder if there are some files in some subfolders )
     * @return Response
     * @throws \Exception
     */
    public function removeFolder(?string $uploadModuleDir, ?string $currentDirectoryPathInModuleUploadDir, bool $blocksRemoval = false) {

        $subdirectoryName = basename($currentDirectoryPathInModuleUploadDir);

        $message = $this->application->translator->translate('logs.directories.startedRemovingFolder');
        $this->logger->info($message, [
            'upload_module_dir' => $uploadModuleDir,
            'subdirectory_name' => $subdirectoryName,
            'current_directory_path_in_upload_type_dir' => $currentDirectoryPathInModuleUploadDir,
             // napiac kiedy bedziemy - data
        ]);

        if( empty($subdirectoryName) )
        {
            $message = $this->application->translator->translate('responses.directories.cannotRemoveMainFolder');
            return new Response($message, 500);
        }

        if( empty($uploadModuleDir) )
        {
            $message = $this->application->translator->translate('responses.directories.youNeedToSelectUploadType');
            return new Response($message, 500);
        }

        $targetUploadDirForModule = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $isSubdirectoryExisting   = !FileUploadController::isSubdirectoryForModuleDirExisting($targetUploadDirForModule, $currentDirectoryPathInModuleUploadDir);
        $subdirectoryPath         = $targetUploadDirForModule.'/'.$currentDirectoryPathInModuleUploadDir;

        if( $isSubdirectoryExisting ){
            $logMessage      = $this->application->translator->translate('logs.directories.removedFolderDoesNotExist');
            $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryDoesNotExistForThisModule');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if( $blocksRemoval ){
            $filesCountInTree = FilesHandler::countFilesInTree($subdirectoryPath);

            if ( $filesCountInTree > 0 ){
                $logMessage      = $this->application->translator->translate('logs.directories.folderRemovalHasBeenBlockedThereAreFilesInside');
                $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryDoesNotExistForThisModule');

                $this->logger->info($logMessage,[
                    'subdirectoryPath' => $subdirectoryPath
                ]);
                return new Response($responseMessage, 500);
            }
        }


        try{
            Utils::removeFolderRecursively($subdirectoryPath);
        }catch(\Exception $e){
            $logMessage      = $this->application->translator->translate('logs.directories.couldNotRemoveFolder');
            $responseMessage = $this->application->translator->translate('responses.directories.errorWhileRemovingSubdirectory');

            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);
            return new Response($responseMessage, 500);
        }

        $logMessage      = $this->application->translator->translate('logs.directories.finishedRemovingFolder');
        $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryHasBeenRemove');

        $this->logger->info($logMessage);
        return new Response($responseMessage);

    }

    /**
     * @Route("/upload/{upload_type}/rename-subdirectory", name="upload_rename_subdirectory", methods="POST")
     * @param string $uploadType
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectoryByPostRequest(string $uploadType, Request $request) {

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME) ) {
            $message = $this->application->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NEW_NAME;
            return new Response($message, 500);
        }

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME) ) {
            $message = $this->application->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME;
            return new Response($message, 500);
        }

        $currentDirectoryPathInModuleUploadDir = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $subdirectoryNewName                   = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME);

        $response = $this->renameSubdirectory($uploadType, $currentDirectoryPathInModuleUploadDir, $subdirectoryNewName);

        return $response;
    }

    /**
     * @param string|null $uploadType
     * @param string|null $currentDirectoryPathInModuleUploadDir
     * @param string|null $subdirectoryNewName
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(?string $uploadType, ?string $currentDirectoryPathInModuleUploadDir, ?string $subdirectoryNewName) {

        $subdirectoryCurrentName = basename($currentDirectoryPathInModuleUploadDir);

        $logMessage = $this->application->translator->translate('logs.directories.startedRenamingFolder');
        $this->logger->info($logMessage, [
            'upload_type'               => $uploadType,
            'subdirectory_current_name' => $subdirectoryCurrentName,
            'subdirectory_new_name'     => $subdirectoryNewName,
            'current_directory_path_in_upload_type_dir' => $currentDirectoryPathInModuleUploadDir
        ]);

        if( $subdirectoryCurrentName === $subdirectoryNewName ){
            $logMessage      = $this->application->translator->translate('logs.directories.subdirectoryNameWillNotChange');
            $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryNameWillNotChange');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($subdirectoryNewName) ){
            $logMessage      = $this->application->translator->translate('logs.directories.subdirectoryNewNameIsEmptyString');
            $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryNewNameIsEmptyString');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($subdirectoryCurrentName) ){
            $logMessage      = $this->application->translator->translate('logs.directories.subdirectoryCurrentNameIsEmptyString');
            $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryCurrentNameIsEmptyString');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($uploadType) ){
            $logMessage      = $this->application->translator->translate('logs.directories.missingUploadModuleType');
            $responseMessage = $this->application->translator->translate('responses.directories.missingUploadModuleType');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        $targetDirectory       = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadType);
        $subdirectoryExists    = FileUploadController::isSubdirectoryForModuleDirExisting($targetDirectory, $currentDirectoryPathInModuleUploadDir);

        $currentDirectoryPath = $targetDirectory.'/'.$currentDirectoryPathInModuleUploadDir;
        $targetDirectory      = dirname($currentDirectoryPath);
        $newDirectoryPath     = $targetDirectory . '/' . $subdirectoryNewName;

        if( !file_exists($currentDirectoryPath) ){
            $logMessage      = $this->application->translator->translate('logs.directories.renamedTargetDirectoryDoesNotExist');
            $responseMessage = $this->application->translator->translate('responses.directories.renamedTargetDirectoryDoesNotExist');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if( !$subdirectoryExists ){
            $logMessage      = $this->application->translator->translate('logs.directories.subdirectoryWithThisNameDoesNotExist');
            $responseMessage = $this->application->translator->translate('responses.directories.subdirectoryWithThisNameDoesNotExist');
            $this->logger->info($logMessage, [
                'targetDirectory'                 => $targetDirectory,
                'currentDirPathInModuleUploadDir' => $currentDirectoryPathInModuleUploadDir
            ]);
            return new Response($responseMessage, 500);
        }

        $subdirectoryWithNewNameExists = FileUploadController::isSubdirectoryForModuleDirExisting($targetDirectory, $subdirectoryNewName);
        if( $subdirectoryWithNewNameExists ){
            $logMessage      = $this->application->translator->translate('logs.directories.renamingSubdirectoryWithThisNameAlreadyExist');
            $responseMessage = $this->application->translator->translate('responses.directories.renamingSubdirectoryWithThisNameAlreadyExist');

            $this->logger->info($logMessage, [
                'new_name'          => $subdirectoryNewName,
                'target_directory'  => $targetDirectory
            ]);
            return new Response($responseMessage, 500);
        }

        try{
            rename($currentDirectoryPath, $newDirectoryPath);
            $this->fileTagger->updateFilePathByFolderPathChange($currentDirectoryPath, $newDirectoryPath);

            $module     = ModulesController::getUploadModuleNameForFileFullPath($currentDirectoryPath);
            $moduleData = $this->moduleDataController->getOneByRecordTypeModuleAndRecordIdentifier(ModuleData::RECORD_TYPE_DIRECTORY, $module, $currentDirectoryPath);

            if( !is_null($moduleData) ){
                $this->moduleDataController->updateRecordIdentifier($moduleData, $newDirectoryPath);
            }

        }catch(\Exception $e){
            $message = $this->application->translator->translate('logs.directories.thereWasAnErrorWhileRenamingFolder');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);

            $message = $this->application->translator->translate('responses.directories.thereWasAnErrorWhileRenamingFolder');
            return new Response($message, 500);
        }

        $logMessage      = $this->application->translator->translate('logs.directories.finishedRenamingFolder');
        $responseMessage = $this->application->translator->translate('responses.directories.folderNameHasBeenSuccessfullyChanged');

        $this->logger->info($logMessage);
        return new Response($responseMessage, 200);

    }

    /**
     * @param string $uploadType
     * @param string $subdirectoryName
     * @param string $targetDirectoryPathInUploadTypeDir
     * @return Response
     * @throws \Exception
     */
    public function createFolder(string $uploadType, string $subdirectoryName, string $targetDirectoryPathInUploadTypeDir){

        $logMessage = $this->application->translator->translate('logs.directories.startedCreatingSubdirectory');

        $this->logger->info($logMessage, [
            'upload_type'       => $uploadType,
            'subdirectory_name' => $subdirectoryName
        ]);

        $targetDirectory = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadType);

        # check if main folder
        if( $targetDirectoryPathInUploadTypeDir === $targetDirectory ){
            $fullSubdirPath = $targetDirectory.'/'.$subdirectoryName;
        }else{
            $fullSubdirPath = $targetDirectory.'/'.$targetDirectoryPathInUploadTypeDir.'/'.$subdirectoryName;
        }

        if( file_exists($fullSubdirPath) ){
            $logMessage      = $this->application->translator->translate('logs.directories.createFoldedThisNameAlreadyExist');
            $responseMessage = $this->application->translator->translate('responses.directories.createFoldedThisNameAlreadyExist');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        try {
            mkdir($fullSubdirPath, 0777);
        } catch (\Exception $e) {
            $logMessage        = $this->application->translator->translate('logs.directories.thereWasAnErrorWhileCreatingFolder');
            $responseMessage   = $this->application->translator->translate('responses.directories.thereWasAnErrorWhileCreatingFolder');

            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);

            return new Response($responseMessage, 500);
        }

        $logMessage        = $this->application->translator->translate('logs.directories.finishedCreatingSubdirectory');
        $responseMessage   = $this->application->translator->translate('responses.directories.subdirectoryForModuleSuccessfullyCreated');

        $this->logger->info($logMessage);
        return new Response ($responseMessage, 200);
    }

    /**
     * @param DirectoryIterator $dir
     * @param bool $useFoldername
     * @param bool $flatten         - if true then returns tree in single dimension array
     * @param bool $includeLocked  - if true then includes also the locked directories (via LockMechanism)
     * @return array
     * @throws Exception
     * @throws DbalException
     * @throws \Exception
     */
    public static function buildFoldersTreeForDirectory(DirectoryIterator $dir, bool $useFoldername = false, bool $flatten = false, bool $includeLocked = false): array
    {
        $data = [];
        foreach ( $dir as $node )
        {
            if ( $node->isDir() && !$node->isDot() )
            {
                $pathname   = $node->getPathname();
                $moduleName = FileUploadController::getUploadModuleNameForFilePath($pathname);
                $foldername = $node->getFilename();
                $key        = ( $useFoldername ? $foldername : $pathname);

                if(
                        !$includeLocked
                    &&  !self::$lockedResourceController->isAllowedToSeeResource($pathname, LockedResource::TYPE_DIRECTORY, $moduleName, false)
                ) {
                    continue; // skip that folder
                }

                if( !$flatten ){
                    $data[$key] = static::buildFoldersTreeForDirectory( new DirectoryIterator( $pathname ) );
                }else{
                    $data[]          = $key;
                    $recursionResult = static::buildFoldersTreeForDirectory( new DirectoryIterator( $pathname ) );
                    $data            = array_merge($data, array_keys($recursionResult));
                    $data            = array_filter($data);
                    $data            = array_unique($data);
                }
            }

        }
        return $data;
    }

    /**
     * @param string $currentFolderPath
     * @param string $parentFolderPath
     * @return Response
     * @throws \Exception
     */
    public function moveDirectory(string $currentFolderPath, string $parentFolderPath): Response{

        # this vars are used to move the folder
        $currentFolderName = basename($currentFolderPath);
        $newFolderPath     = $parentFolderPath . DIRECTORY_SEPARATOR . $currentFolderName;
        $mainUploadDirs    = Env::getUploadDirs();

        if( in_array($currentFolderPath, $mainUploadDirs) ){
            $message = $this->application->translator->translate('responses.directories.cannotMoveModuleMainUploadDir');
            return new Response($message, 500);
        }

        if( file_exists($newFolderPath) ){
            $message = $this->application->translator->translate('responses.directories.directoryWithThisNameAlreadyExistInTargetFolder');
            return new Response($message, 500);
        }

        if( !file_exists($currentFolderPath) ){
            $message = $this->application->translator->translate('responses.directories.theDirectoryYouTryToMoveDoesNotExist');
            return new Response($message, 500);
        }

        if( $currentFolderPath === $parentFolderPath ){
            $message = $this->application->translator->translate('responses.directories.currentDirectoryPathIsTheSameAsNewPath');
            return new Response($message, 500);
        }

        if( strstr($parentFolderPath, $currentFolderPath) ){
            $message = $this->application->translator->translate('responses.directories.cannotMoveFolderInsideItsOwnSubfolder');
            return new Response($message, 500);
        }

        $this->finder->files()->in($currentFolderPath);

        try{

             /**
             * Update tagger path for each file that has tags
             * @var File $file
             */
            foreach( $this->finder as $file ){

                # this vars are only used to update tags
                $currentFilePath = $file->getPathname();
                $currentFileName = $file->getFilename();

                $fileNewDirPath  = self::getFolderPathWithoutUploadDirForFolderPath($newFolderPath);
                $moduleUploadDir = self::getUploadDirForFilePath($parentFolderPath);

                $newFilePath = $moduleUploadDir . DIRECTORY_SEPARATOR . $fileNewDirPath . DIRECTORY_SEPARATOR . $currentFileName;

                $this->fileTagger->updateFilePath($currentFilePath, $newFilePath);
            }

            # Info: rename is using for handling file moving
            rename($currentFolderPath, $newFolderPath);
            $this->application->repositories->lockedResourceRepository->updatePath($currentFolderPath, $newFolderPath);

            $module     = ModulesController::getUploadModuleNameForFileFullPath($currentFolderPath);
            $moduleData = $this->moduleDataController->getOneByRecordTypeModuleAndRecordIdentifier(ModuleData::RECORD_TYPE_DIRECTORY, $module, $currentFolderPath);

            if( !is_null($moduleData) ){
                $this->moduleDataController->updateRecordIdentifier($moduleData, $newFolderPath);
            }
        }catch(\Exception $e){
            return new Response($e->getMessage(), $e->getCode());
        }

        $message = $this->application->translator->translate('responses.directories.directoryHasBeenSuccessfullyMoved');
        return new Response($message, 200);
    }

    /**
     * This function will strip upload dir for module from folder path if folder contains the upload dir
     * it will not check if the upload dir is on the beginning so passing the absolute path will fail
     * @param string $folderPath (relative)
     * @return string
     */
    public static function getFolderPathWithoutUploadDirForFolderPath(string $folderPath): string{
        $uploadDirs    = Env::getUploadDirs();
        $modifiedPath  = $folderPath;

        foreach($uploadDirs as $uploadDir){

            if( strstr($folderPath, $uploadDir) ){
                $modifiedPath = str_replace($uploadDir, "", $folderPath);
            }

        }

        $strippedPath = $modifiedPath;
        return FilesHandler::trimFirstAndLastSlash($strippedPath);
    }

    /**
     * This function will return null or string if the upload dir is found in the file_path
     * it will not check if the upload dir is on the beginning so passing the absolute path will fail
     * @param string $filePath
     * @return string | null
     */
    public static function getUploadDirForFilePath(string $filePath): ?string{
        $uploadDirs = Env::getUploadDirs();

        foreach($uploadDirs as $uploadDir){

            if( strstr($filePath, $uploadDir) ){
                return $uploadDir;
            }

        }

        return null;
    }


}