<?php

namespace App\Services\Files;

use App\Controller\Files\FileUploadController;
use App\Entity\Modules\ModuleData;
use App\Entity\System\LockedResource;
use App\Repository\Modules\ModuleDataRepository;
use App\Services\Module\ModulesService;
use App\Services\System\LockedResourceService;
use App\Services\Utils;
use DirectoryIterator;
use Doctrine\DBAL\Driver\Exception as DbalException;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This service is responsible for handling folders in terms of internal usage, like moving/renaming/etc...
 * Class DirectoriesHandler
 * @package App\Services
 */
class DirectoriesHandler {

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
     * Info: must remain static due to the static methods requiring this logic
     * @var LockedResourceService $lockedResourceService
     */
    private static LockedResourceService $lockedResourceService;

    public function __construct(
        LoggerInterface                       $logger,
        FileTagger                            $fileTagger,
        LockedResourceService                 $lockedResourceService,
        private readonly TranslatorInterface  $translator,
        private readonly ModuleDataRepository $moduleDataRepository,
    ) {
        self::$lockedResourceService = $lockedResourceService;
        $this->logger                = $logger;
        $this->finder                   = new Finder();
        $this->fileTagger               = $fileTagger;
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

        $message = $this->translator->trans('logs.directories.startedRemovingFolder');
        $this->logger->info($message, [
            'upload_module_dir' => $uploadModuleDir,
            'subdirectory_name' => $subdirectoryName,
            'current_directory_path_in_upload_type_dir' => $currentDirectoryPathInModuleUploadDir,
        ]);

        if( empty($subdirectoryName) )
        {
            $message = $this->translator->trans('responses.directories.cannotRemoveMainFolder');
            return new Response($message, 500);
        }

        if( empty($uploadModuleDir) )
        {
            $message = $this->translator->trans('responses.directories.youNeedToSelectUploadType');
            return new Response($message, 500);
        }

        $targetUploadDirForModule = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $isSubdirectoryExisting   = !FileUploadController::isSubdirectoryForModuleDirExisting($targetUploadDirForModule, $currentDirectoryPathInModuleUploadDir);
        $subdirectoryPath         = $targetUploadDirForModule.'/'.$currentDirectoryPathInModuleUploadDir;

        if( $isSubdirectoryExisting ){
            $logMessage      = $this->translator->trans('logs.directories.removedFolderDoesNotExist');
            $responseMessage = $this->translator->trans('responses.directories.subdirectoryDoesNotExistForThisModule');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if( $blocksRemoval ){
            $filesCountInTree = FilesHandler::countFilesInTree($subdirectoryPath);

            if ( $filesCountInTree > 0 ){
                $logMessage      = $this->translator->trans('logs.directories.folderRemovalHasBeenBlockedThereAreFilesInside');
                $responseMessage = $this->translator->trans('responses.directories.subdirectoryDoesNotExistForThisModule');

                $this->logger->info($logMessage,[
                    'subdirectoryPath' => $subdirectoryPath
                ]);
                return new Response($responseMessage, 500);
            }
        }


        try{
            Utils::removeFolderRecursively($subdirectoryPath);
        }catch(\Exception $e){
            $logMessage      = $this->translator->trans('logs.directories.couldNotRemoveFolder');
            $responseMessage = $this->translator->trans('responses.directories.errorWhileRemovingSubdirectory');

            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);
            return new Response($responseMessage, 500);
        }

        $logMessage      = $this->translator->trans('logs.directories.finishedRemovingFolder');
        $responseMessage = $this->translator->trans('responses.directories.subdirectoryHasBeenRemove');

        $this->logger->info($logMessage);
        return new Response($responseMessage);

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

        $logMessage = $this->translator->trans('logs.directories.startedRenamingFolder');
        $this->logger->info($logMessage, [
            'upload_type'               => $uploadType,
            'subdirectory_current_name' => $subdirectoryCurrentName,
            'subdirectory_new_name'     => $subdirectoryNewName,
            'current_directory_path_in_upload_type_dir' => $currentDirectoryPathInModuleUploadDir
        ]);

        if( $subdirectoryCurrentName === $subdirectoryNewName ){
            $logMessage      = $this->translator->trans('logs.directories.subdirectoryNameWillNotChange');
            $responseMessage = $this->translator->trans('responses.directories.subdirectoryNameWillNotChange');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($subdirectoryNewName) ){
            $logMessage      = $this->translator->trans('logs.directories.subdirectoryNewNameIsEmptyString');
            $responseMessage = $this->translator->trans('responses.directories.subdirectoryNewNameIsEmptyString');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($subdirectoryCurrentName) ){
            $logMessage      = $this->translator->trans('logs.directories.subdirectoryCurrentNameIsEmptyString');
            $responseMessage = $this->translator->trans('responses.directories.subdirectoryCurrentNameIsEmptyString');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if ( empty($uploadType) ){
            $logMessage      = $this->translator->trans('logs.directories.missingUploadModuleType');
            $responseMessage = $this->translator->trans('responses.directories.missingUploadModuleType');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        $targetDirectory       = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadType);
        $subdirectoryExists    = FileUploadController::isSubdirectoryForModuleDirExisting($targetDirectory, $currentDirectoryPathInModuleUploadDir);

        $currentDirectoryPath = $targetDirectory.'/'.$currentDirectoryPathInModuleUploadDir;
        $targetDirectory      = dirname($currentDirectoryPath);
        $newDirectoryPath     = $targetDirectory . '/' . $subdirectoryNewName;

        if( !file_exists($currentDirectoryPath) ){
            $logMessage      = $this->translator->trans('logs.directories.renamedTargetDirectoryDoesNotExist');
            $responseMessage = $this->translator->trans('responses.directories.renamedTargetDirectoryDoesNotExist');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        if( !$subdirectoryExists ){
            $logMessage      = $this->translator->trans('logs.directories.subdirectoryWithThisNameDoesNotExist');
            $responseMessage = $this->translator->trans('responses.directories.subdirectoryWithThisNameDoesNotExist');
            $this->logger->info($logMessage, [
                'targetDirectory'                 => $targetDirectory,
                'currentDirPathInModuleUploadDir' => $currentDirectoryPathInModuleUploadDir
            ]);
            return new Response($responseMessage, 500);
        }

        $subdirectoryWithNewNameExists = FileUploadController::isSubdirectoryForModuleDirExisting($targetDirectory, $subdirectoryNewName);
        if( $subdirectoryWithNewNameExists ){
            $logMessage      = $this->translator->trans('logs.directories.renamingSubdirectoryWithThisNameAlreadyExist');
            $responseMessage = $this->translator->trans('responses.directories.renamingSubdirectoryWithThisNameAlreadyExist');

            $this->logger->info($logMessage, [
                'new_name'          => $subdirectoryNewName,
                'target_directory'  => $targetDirectory
            ]);
            return new Response($responseMessage, 500);
        }

        try{
            rename($currentDirectoryPath, $newDirectoryPath);
            $this->fileTagger->updateFilePathByFolderPathChange($currentDirectoryPath, $newDirectoryPath);

            $module     = ModulesService::getUploadModuleNameForFileFullPath($currentDirectoryPath);
            $moduleData = $this->moduleDataRepository->getOneByRecordTypeModuleAndRecordIdentifier(ModuleData::RECORD_TYPE_DIRECTORY, $module, $currentDirectoryPath);

            if( !is_null($moduleData) ){
                $moduleData->setRecordIdentifier($newDirectoryPath);
                $this->moduleDataRepository->saveEntity($moduleData);
            }

        }catch(\Exception $e){
            $message = $this->translator->trans('logs.directories.thereWasAnErrorWhileRenamingFolder');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);

            $message = $this->translator->trans('responses.directories.thereWasAnErrorWhileRenamingFolder');
            return new Response($message, 500);
        }

        $logMessage      = $this->translator->trans('logs.directories.finishedRenamingFolder');
        $responseMessage = $this->translator->trans('responses.directories.folderNameHasBeenSuccessfullyChanged');

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

        $logMessage = $this->translator->trans('logs.directories.startedCreatingSubdirectory');

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
            $logMessage      = $this->translator->trans('logs.directories.createFoldedThisNameAlreadyExist');
            $responseMessage = $this->translator->trans('responses.directories.createFoldedThisNameAlreadyExist');

            $this->logger->info($logMessage);
            return new Response($responseMessage, 500);
        }

        try {
            mkdir($fullSubdirPath, 0777);
        } catch (\Exception $e) {
            $logMessage        = $this->translator->trans('logs.directories.thereWasAnErrorWhileCreatingFolder');
            $responseMessage   = $this->translator->trans('responses.directories.thereWasAnErrorWhileCreatingFolder');

            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);

            return new Response($responseMessage, 500);
        }

        $logMessage        = $this->translator->trans('logs.directories.finishedCreatingSubdirectory');
        $responseMessage   = $this->translator->trans('responses.directories.subdirectoryForModuleSuccessfullyCreated');

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
                    &&  !self::$lockedResourceService->isAllowedToSeeResource($pathname, LockedResource::TYPE_DIRECTORY, $moduleName, false)
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

}