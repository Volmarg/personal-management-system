<?php

namespace App\Services;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Utils;
use DirectoryIterator;
use Psr\Log\LoggerInterface;
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
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(Application $application, LoggerInterface $logger,  FileTagger $file_tagger) {
        $this->application = $application;
        $this->logger      = $logger;
        $this->file_tagger = $file_tagger;
    }

    /**
     * @param string $upload_module_dir
     * @param string $current_directory_path_in_module_upload_dir
     * @param bool $blocks_removal ( will prevent removing folder if there are some files in some subfolders )
     * @return Response
     * @throws \Exception
     */
    public function removeFolder(?string $upload_module_dir, ?string $current_directory_path_in_module_upload_dir, bool $blocks_removal = false) {

        $subdirectory_name = basename($current_directory_path_in_module_upload_dir);

        $message = $this->application->translator->translate('logs.directories.startedRemovingFolder');
        $this->logger->info($message, [
            'upload_module_dir' => $upload_module_dir,
            'subdirectory_name' => $subdirectory_name,
            'current_directory_path_in_upload_type_dir' => $current_directory_path_in_module_upload_dir,
             // napiac kiedy bedziemy - data
        ]);

        if( empty($subdirectory_name) )
        {
            $message = $this->application->translator->translate('responses.directories.cannotRemoveMainFolder');
            return new Response($message, 500);
        }

        if( empty($upload_module_dir) )
        {
            $message = $this->application->translator->translate('responses.directories.youNeedToSelectUploadType');
            return new Response($message, 500);
        }

        $target_upload_dir_for_module = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $is_subdirectory_existing     = !FileUploadController::isSubdirectoryForModuleDirExisting($target_upload_dir_for_module, $current_directory_path_in_module_upload_dir);
        $subdirectory_path            = $target_upload_dir_for_module.'/'.$current_directory_path_in_module_upload_dir;

        if( $is_subdirectory_existing ){
            $log_message      = $this->application->translator->translate('logs.directories.removedFolderDoesNotExist');
            $response_message = $this->application->translator->translate('responses.directories.subdirectoryDoesNotExistForThisModule');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if( $blocks_removal ){
            $files_count_in_tree = FilesHandler::countFilesInTree($subdirectory_path);

            if ( $files_count_in_tree > 0 ){
                $log_message      = $this->application->translator->translate('logs.directories.folderRemovalHasBeenBlockedThereAreFilesInside');
                $response_message = $this->application->translator->translate('responses.directories.subdirectoryDoesNotExistForThisModule');

                $this->logger->info($log_message,[
                    'subdirectoryPath' => $subdirectory_path
                ]);
                return new Response($response_message, 500);
            }
        }


        try{
            Utils::removeFolderRecursively($subdirectory_path);
        }catch(\Exception $e){
            $log_message      = $this->application->translator->translate('logs.directories.couldNotRemoveFolder');
            $response_message = $this->application->translator->translate('responses.directories.errorWhileRemovingSubdirectory');

            $this->logger->info($log_message, [
                'message' => $e->getMessage()
            ]);
            return new Response($response_message, 500);
        }

        $log_message      = $this->application->translator->translate('logs.directories.finishedRemovingFolder');
        $response_message = $this->application->translator->translate('responses.directories.subdirectoryHasBeenRemove');

        $this->logger->info($log_message);
        return new Response($response_message);

    }

    /**
     * @Route("/upload/{upload_type}/rename-subdirectory", name="upload_rename_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectoryByPostRequest(string $upload_type, Request $request) {

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME) ) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NEW_NAME;
            return new Response($message, 500);
        }

        if ( !$request->query->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME) ) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME;
            return new Response($message, 500);
        }

        $current_directory_path_in_module_upload_dir  = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $subdirectory_new_name                        = $request->query->get(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME);

        $response = $this->renameSubdirectory($upload_type, $current_directory_path_in_module_upload_dir, $subdirectory_new_name);

        return $response;
    }

    /**
     * @param string $upload_type
     * @param string $current_directory_path_in_module_upload_dir
     * @param string $subdirectory_new_name
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(?string $upload_type, ?string $current_directory_path_in_module_upload_dir, ?string $subdirectory_new_name) {

        $subdirectory_current_name = basename($current_directory_path_in_module_upload_dir);

        $log_message = $this->application->translator->translate('logs.directories.startedRenamingFolder');
        $this->logger->info($log_message, [
            'upload_type'               => $upload_type,
            'subdirectory_current_name' => $subdirectory_current_name,
            'subdirectory_new_name'     => $subdirectory_new_name,
            'current_directory_path_in_upload_type_dir' => $current_directory_path_in_module_upload_dir
        ]);

        if( $subdirectory_current_name === $subdirectory_new_name ){
            $log_message      = $this->application->translator->translate('logs.directories.subdirectoryNameWillNotChange');
            $response_message = $this->application->translator->translate('responses.directories.subdirectoryNameWillNotChange');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if ( empty($subdirectory_new_name) ){
            $log_message      = $this->application->translator->translate('logs.directories.subdirectoryNewNameIsEmptyString');
            $response_message = $this->application->translator->translate('responses.directories.subdirectoryNewNameIsEmptyString');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if ( empty($subdirectory_current_name) ){
            $log_message      = $this->application->translator->translate('logs.directories.subdirectoryCurrentNameIsEmptyString');
            $response_message = $this->application->translator->translate('responses.directories.subdirectoryCurrentNameIsEmptyString');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if ( empty($upload_type) ){
            $log_message      = $this->application->translator->translate('logs.directories.missingUploadModuleType');
            $response_message = $this->application->translator->translate('responses.directories.missingUploadModuleType');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        $target_directory       = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_type);
        $subdirectory_exists    = FileUploadController::isSubdirectoryForModuleDirExisting($target_directory, $current_directory_path_in_module_upload_dir);

        $current_directory_path = $target_directory.'/'.$current_directory_path_in_module_upload_dir;
        $target_directory       = dirname($current_directory_path);
        $new_directory_path     = $target_directory . '/' . $subdirectory_new_name;

        if( !file_exists($current_directory_path) ){
            $log_message      = $this->application->translator->translate('logs.directories.renamedTargetDirectoryDoesNotExist');
            $response_message = $this->application->translator->translate('responses.directories.renamedTargetDirectoryDoesNotExist');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        if( !$subdirectory_exists ){
            $log_message      = $this->application->translator->translate('logs.directories.subdirectoryWithThisNameDoesNotExist');
            $response_message = $this->application->translator->translate('responses.directories.subdirectoryWithThisNameDoesNotExist');
            $this->logger->info($log_message, [
                'targetDirectory'                 => $target_directory,
                'currentDirPathInModuleUploadDir' => $current_directory_path_in_module_upload_dir
            ]);
            return new Response($response_message, 500);
        }

        $subdirectory_with_new_name_exists = FileUploadController::isSubdirectoryForModuleDirExisting($target_directory, $subdirectory_new_name);

        if( $subdirectory_with_new_name_exists ){
            $log_message      = $this->application->translator->translate('logs.directories.renamingSubdirectoryWithThisNameAlreadyExist');
            $response_message = $this->application->translator->translate('responses.directories.renamingSubdirectoryWithThisNameAlreadyExist');

            $this->logger->info($log_message, [
                'new_name'          => $subdirectory_new_name,
                'target_directory'  => $target_directory
            ]);
            return new Response($response_message, 500);
        }

        try{
            rename($current_directory_path, $new_directory_path);
            $this->file_tagger->updateFilePathByFolderPathChange($current_directory_path, $new_directory_path);
        }catch(\Exception $e){
            $message = $this->application->translator->translate('logs.directories.thereWasAnErrorWhileRenamingFolder');
            $this->logger->info($message, [
                'message' => $e->getMessage()
            ]);

            $message = $this->application->translator->translate('responses.directories.thereWasAnErrorWhileRenamingFolder');
            return new Response($message, 500);
        }

        $log_message      = $this->application->translator->translate('logs.directories.finishedRenamingFolder');
        $response_message = $this->application->translator->translate('responses.directories.folderNameHasBeenSuccessfullyChanged');

        $this->logger->info($log_message);
        return new Response($response_message, 200);

    }

    /**
     * @param string $upload_type
     * @param string $subdirectory_name
     * @param string $target_directory_path_in_upload_type_dir
     * @return Response
     * @throws \Exception
     */
    public function createFolder(string $upload_type, string $subdirectory_name, string $target_directory_path_in_upload_type_dir){

        $log_message = $this->application->translator->translate('logs.directories.startedCreatingSubdirectory');

        $this->logger->info($log_message, [
            'upload_type'       => $upload_type,
            'subdirectory_name' => $subdirectory_name
        ]);

        $target_directory       = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_type);

        # check if main folder
        if( $target_directory_path_in_upload_type_dir === $target_directory ){
            $full_subdir_path = $target_directory.'/'.$subdirectory_name;
        }else{
            $full_subdir_path = $target_directory.'/'.$target_directory_path_in_upload_type_dir.'/'.$subdirectory_name;
        }

        if( file_exists($full_subdir_path) ){
            $log_message        = $this->application->translator->translate('logs.directories.createFoldedThisNameAlreadyExist');
            $response_message   = $this->application->translator->translate('responses.directories.createFoldedThisNameAlreadyExist');

            $this->logger->info($log_message);
            return new Response($response_message, 500);
        }

        try {
            mkdir($full_subdir_path, 0777);
        } catch (\Exception $e) {
            $log_message        = $this->application->translator->translate('logs.directories.thereWasAnErrorWhileCreatingFolder');
            $response_message   = $this->application->translator->translate('responses.directories.thereWasAnErrorWhileCreatingFolder');

            $this->logger->info($log_message, [
                'message' => $e->getMessage()
            ]);

            return new Response($response_message, 500);
        }

        $log_message        = $this->application->translator->translate('logs.directories.finishedCreatingSubdirectory');
        $response_message   = $this->application->translator->translate('responses.directories.subdirectoryForModuleSuccessfullyCreated');

        $this->logger->info($log_message);
        return new Response ($response_message, 200);
    }

    /**
     * @param DirectoryIterator $dir
     * @param bool $use_foldername
     * @return array
     */
    public static function buildFoldersTreeForDirectory(DirectoryIterator $dir, bool $use_foldername = false): array
    {
        $data = [];
        foreach ( $dir as $node )
        {
            if ( $node->isDir() && !$node->isDot() )
            {
                $pathname        = $node->getPathname();
                $foldername      = $node->getFilename();
                $key             = ( $use_foldername ? $foldername : $pathname);

                $data[$key] = static::buildFoldersTreeForDirectory( new DirectoryIterator( $pathname ) );
            }

        }
        return $data;
    }

    public static function buildFoldersTreeForDirectories(array $directories, bool $use_foldername = false): array {
        $directories_trees = [];

        foreach ($directories as $directory) {
            $directory_tree                 = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $directory ), $use_foldername );
            $directories_trees[$directory]  = $directory_tree;
        }

        return $directories_trees;
    }

}