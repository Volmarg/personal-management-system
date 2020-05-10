<?php

namespace App\Services\Files;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Controller\Utils\Utils;
use DirectoryIterator;
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
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    public function __construct(Application $application, LoggerInterface $logger,  FileTagger $file_tagger, FilesTagsController $files_tags_controller) {
        $this->application           = $application;
        $this->logger                = $logger;
        $this->finder                = new Finder();
        $this->file_tagger           = $file_tagger;
        $this->files_tags_controller = $files_tags_controller;
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

    /**
     * @param string $current_folder_path
     * @param string $parent_folder_path
     * @return Response
     * @throws \Exception
     */
    public function moveDirectory(string $current_folder_path, string $parent_folder_path): Response{

        # this vars are used to move the folder
        $current_folder_name = basename($current_folder_path);
        $new_folder_path     = $parent_folder_path . DIRECTORY_SEPARATOR . $current_folder_name;
        $main_upload_dirs    = Env::getUploadDirs();

        if( in_array($current_folder_path, $main_upload_dirs) ){
            $message = $this->application->translator->translate('responses.directories.cannotMoveModuleMainUploadDir');
            return new Response($message, 500);
        }

        if( file_exists($new_folder_path) ){
            $message = $this->application->translator->translate('responses.directories.directoryWithThisNameAlreadyExistInTargetFolder');
            return new Response($message, 500);
        }

        if( !file_exists($current_folder_path) ){
            $message = $this->application->translator->translate('responses.directories.theDirectoryYouTryToMoveDoesNotExist');
            return new Response($message, 500);
        }

        if( $current_folder_path === $parent_folder_path ){
            $message = $this->application->translator->translate('responses.directories.currentDirectoryPathIsTheSameAsNewPath');
            return new Response($message, 500);
        }

        if( strstr($parent_folder_path, $current_folder_path) ){
            $message = $this->application->translator->translate('responses.directories.cannotMoveFolderInsideItsOwnSubfolder');
            return new Response($message, 500);
        }

        $this->finder->files()->in($current_folder_path);

        try{

             /**
             * Update tagger path for each file that has tags
             * @var File $file
             */
            foreach( $this->finder as $file ){

                # this vars are only used to update tags
                $current_file_path = $file->getPathname();
                $current_file_name = $file->getFilename();

                $file_new_dir_path  = self::getFolderPathWithoutUploadDirForFolderPath($new_folder_path);
                $module_upload_dir  = self::getUploadDirForFilePath($parent_folder_path);

                $new_file_path = $module_upload_dir . DIRECTORY_SEPARATOR . $file_new_dir_path . DIRECTORY_SEPARATOR . $current_file_name;

                $this->file_tagger->updateFilePath($current_file_path, $new_file_path);
            }

            # Info: rename is using for handling file moving
            rename($current_folder_path, $new_folder_path);
            $this->application->repositories->lockedResourceRepository->updatePath($current_folder_path, $new_folder_path);

        }catch(\Exception $e){
            return new Response($e->getMessage(), $e->getCode());
        }

        $message = $this->application->translator->translate('responses.directories.directoryHasBeenSuccessfullyMoved');
        return new Response($message, 200);
    }

    /**
     * This function will strip upload dir for module from folder path if folder contains the upload dir
     * it will not check if the upload dir is on the beginning so passing the absolute path will fail
     * @param string $folder_path (relative)
     * @return string
     */
    public static function getFolderPathWithoutUploadDirForFolderPath(string $folder_path): string{
        $upload_dirs    = Env::getUploadDirs();
        $modified_path  = $folder_path;

        foreach($upload_dirs as $upload_dir){

            if( strstr($folder_path, $upload_dir) ){
                $modified_path = str_replace($upload_dir, "", $folder_path);
            }

        }

        $stripped_path = $modified_path;
        return FilesHandler::trimFirstAndLastSlash($stripped_path);
    }

    /**
     * This function will return null or string if the upload dir is found in the file_path
     * it will not check if the upload dir is on the beginning so passing the absolute path will fail
     * @param string $file_path
     * @return string | null
     */
    public static function getUploadDirForFilePath(string $file_path): ?string{
        $upload_dirs = Env::getUploadDirs();

        foreach($upload_dirs as $upload_dir){

            if( strstr($file_path, $upload_dir) ){
                return $upload_dir;
            }

        }

        return null;
    }


}