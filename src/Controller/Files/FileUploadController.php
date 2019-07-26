<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Controller\Utils\Utils;
use App\Form\UploadFormType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    # TODO: add moving subdirectories to other folder
        # But do it by copying and removing if copying was done
        # If copying was not done correctly then warn user about it - he should remove this on his own
        # maybe moving progress?
    # TODO: add moving content of one folder to another
    # TODO: handle case when user tries to enter non existing folder via url
    # TODO: add handling folder creation in settings
    # TODO: add logging for most crucial parts, like removing/copying data
    # TODO: rethink if all the advanced logic of moving files/removing dirs shouldn't bee in some case of FilesAndFoldersService?
    #   Or both: FilesService and FoldersService
    #   This is no longer part of upload, so files service would be best
    # TODO: recheck all consts in forms as well
    # TODO: most likely there will be new module required Upload because the uploading mechnisms are no loner just part of images/files uploads
    #   with this I will need to rethink putting MyImages and MyFiles into Upload module - if I split all the logic

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';
    const FILE_KEY                      = 'file';
    const SUBDIRECTORY_KEY              = 'subdirectory';
    const TYPE_IMAGES                   = 'images';
    const TYPE_FILES                    = 'files';

    const KEY_SUBDIRECTORY_NEW_NAME     = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME = 'subdirectory_current_name';

    const KEY_SUBDIRECTORY_NAME         = 'subdirectory_name';

    const KEY_CURRENT_UPLOAD_TYPE       = 'current_upload_type';
    const KEY_TARGET_UPLOAD_TYPE        = 'target_upload_type';
    const KEY_CURRENT_SUBDIRECTORY_NAME = 'current_subdirectory_name';
    const KEY_TARGET_SUBDIRECTORY_NAME  = 'target_subdirectory_name';

    const UPLOAD_TYPES = [
        self::TYPE_IMAGES => self::TYPE_IMAGES,
        self::TYPE_FILES  => self::TYPE_FILES
    ];

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FileUploader $fileUploader, Application $app) {
        $this->fileUploader = $fileUploader;
        $this->app          = $app;
    }

    /**
     * @Route("/upload/{upload_type}", name="upload")
     * @param Request $request
     * @param string $upload_type
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function upload(Request $request, string $upload_type){

        $allowed_types  = [
            static:: TYPE_IMAGES,
            static:: TYPE_FILES
        ];

        if(!in_array($upload_type, $allowed_types)){
            throw new \Exception('This upload type is not allowed');
        }

        $subdirectories = static::getSubdirectoriesForUploadType($upload_type);

        $form = $this->getUploadForm($subdirectories);
        $this->handleFileUpload($request, $upload_type, $form);

        $data = [
            'ajax_render'       => false,
            'form'              => $form->createView()
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);
    }

    /**
     * @Route("/upload/action/copy-and-remove-folder-data", name="upload_copy_and_remove_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function copyAndRemoveDataViaPost(Request $request) {

        if ( !$request->query->has(static::KEY_CURRENT_SUBDIRECTORY_NAME) ) {
            return new Response("Current subdirectory name is missing in request.");
        }

        if ( !$request->query->has(static::KEY_CURRENT_SUBDIRECTORY_NAME) ) {
            return new Response("Subdirectory current name is missing in request.");
        }
        $current_upload_type        = $request->query->get(static::KEY_CURRENT_UPLOAD_TYPE);
        $current_subdirectory_name  = $request->query->get(static::KEY_CURRENT_SUBDIRECTORY_NAME);

        try{
            $this->copyFolderDataToAnotherFolderByPostRequest($request);
            $this->removeFolder($current_upload_type, $current_subdirectory_name);
        }catch(\Exception $e){
            return new Response ('Then was an error while copying and removing data.');
        }

        return new Response('Data has been successfully copied and removed afterward.');
    }

    /**
     * @param string $current_upload_type
     * @param string $target_upload_type
     * @param string $current_subdirectory_name
     * @param string $target_subdirectory_name
     * @param bool $remove_current_folder
     * @return Response
     */
    public function copyAndRemoveData(
        string $current_upload_type,
        string $target_upload_type,
        string $current_subdirectory_name,
        string $target_subdirectory_name,
        bool   $remove_current_folder = true
    ) {

        try{
            $this->copyFolderDataToAnotherFolder($current_upload_type, $target_upload_type, $current_subdirectory_name, $target_subdirectory_name);
            if($remove_current_folder){
                $this->removeFolder($current_upload_type, $current_subdirectory_name);
            }
        }catch(\Exception $e){
            return new Response ('Then was an error while copying and removing data.');
        }

        return new Response('Data has been successfully copied and removed afterward.');
    }

    /**
     * @Route("/upload/{upload_type}/rename-subdirectory", name="upload_rename_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectoryByPostRequest(string $upload_type, Request $request) {

        if ( !$request->query->has(static::KEY_SUBDIRECTORY_NEW_NAME) ) {
            return new Response("Subdirectory new name is missing in request.");
        }

        if ( !$request->query->has(static::KEY_SUBDIRECTORY_CURRENT_NAME) ) {
            return new Response("Subdirectory current name is missing in request.");
        }

        $subdirectory_current_name  = $request->query->get(static::KEY_SUBDIRECTORY_CURRENT_NAME);
        $subdirectory_new_name      = $request->query->get(static::KEY_SUBDIRECTORY_NEW_NAME);

        $response = $this->renameSubdirectory($upload_type, $subdirectory_current_name, $subdirectory_new_name);

        return $response;
    }

    /**
     * @param string $upload_type
     * @param string $subdirectory_current_name
     * @param string $subdirectory_new_name
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(string $upload_type, string $subdirectory_current_name, string $subdirectory_new_name) {

        if( $subdirectory_current_name === $subdirectory_new_name ){
            return new Response("You are trying to change folder name to the same that there already is - action aborted.");
        }

        if ( empty($subdirectory_new_name) ){
            return new Response('New name is an empty string - action aborted');
        }

        $target_directory       = static::getTargetDirectoryForUploadType($upload_type);
        $subdirectory_exists    = static::isSubdirectoryForTypeExisting($target_directory, $subdirectory_current_name);

        if( !$subdirectory_exists ){
            return new Response("Subdirectory with this name does not exist!");
        }

        $subdirectory_with_new_name_exists = static::isSubdirectoryForTypeExisting($target_directory, $subdirectory_new_name);

        if( $subdirectory_with_new_name_exists ){
            return new Response(" Cannot change folder name! Folder with this name already exist.");
        }

        try{
            $old_folder_location = $target_directory.'/'.$subdirectory_current_name;
            $new_folder_location = $target_directory.'/'.$subdirectory_new_name;
            rename($old_folder_location, $new_folder_location);
        }catch(\Exception $e){
            return new Response('There was an error when renaming the folder! Error message. Most likely due to unallowed characters used in name.');
        }

        return new Response('Folder name has been successfully changed');

    }

    /**
     * @Route("/upload/{upload_type}/remove-subdirectory", name="upload_remove_subdirectory", methods="POST")
     * @param string $upload_type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFolderByPostRequest(string $upload_type, Request $request){

        if ( !$request->query->has(static::KEY_SUBDIRECTORY_NAME) ) {
            return new Response("Subdirectory name is missing in request.");
        }

        $subdirectory_name  = $request->query->get(static::KEY_SUBDIRECTORY_NAME);
        $response           = $this->removeFolder($upload_type, $subdirectory_name);

        return $response;
    }

    /**
     * @param string $upload_type
     * @param string $subdirectory_name
     * @return Response
     * @throws \Exception
     */
    public function removeFolder(string $upload_type, string $subdirectory_name){

        $target_directory = static::getTargetDirectoryForUploadType($upload_type);

        $is_subdirectory_existing = !static::isSubdirectoryForTypeExisting($target_directory, $subdirectory_name);

        if( $is_subdirectory_existing ){
            return new Response('This subdirectory does not exist for current upload type.');
        }

        $subdirectory_path = static::getSubdirectoryPath($target_directory, $subdirectory_name);

        try{
            Utils::removeFolderRecursively($subdirectory_path);
        }catch(\Exception $e){
            return new Response('There was and error when trying to remove subdirectory!');
        }

        return new Response('Subdirectory has been successfully removed.');

    }


    /**
    * @Route("/upload/action/copy-folder-data", name="upload_copy_folder_data", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function copyFolderDataToAnotherFolderByPostRequest(Request $request) {

        if ( !$request->query->has(static::KEY_CURRENT_UPLOAD_TYPE) ) {
            return new Response("Current upload type is missing in request.");
        }

        if ( !$request->query->has(static::KEY_TARGET_UPLOAD_TYPE) ) {
            return new Response("Target upload type is missing in request.");
        }

        if ( !$request->query->has(static::KEY_CURRENT_SUBDIRECTORY_NAME) ) {
            return new Response("Current subdirectory name is missing in request.");
        }

        if ( !$request->query->has(static::KEY_TARGET_SUBDIRECTORY_NAME) ) {
            return new Response("Target subdirectory name is missing in request.");
        }

        $current_upload_type        = $request->query->get(static::KEY_CURRENT_UPLOAD_TYPE);
        $target_upload_type         = $request->query->get(static::KEY_TARGET_UPLOAD_TYPE);
        $current_subdirectory_name  = $request->query->get(static::KEY_CURRENT_SUBDIRECTORY_NAME);
        $target_subdirectory_name   = $request->query->get(static::KEY_TARGET_SUBDIRECTORY_NAME);

       $response = $this->copyFolderDataToAnotherFolder($current_upload_type, $target_upload_type, $current_subdirectory_name, $target_subdirectory_name);

       return $response;
    }


    /**
     * @param string $current_upload_type
     * @param string $target_upload_type
     * @param string $current_subdirectory_name
     * @param string $target_subdirectory_name
     * @return Response
     * @throws \Exception
     */
    public function copyFolderDataToAnotherFolder(string $current_upload_type, string $target_upload_type, string $current_subdirectory_name, string $target_subdirectory_name){

        $current_directory  = static::getTargetDirectoryForUploadType($current_upload_type);
        $target_directory   = static::getTargetDirectoryForUploadType($target_upload_type);

        $is_current_subdirectory_existing = !static::isSubdirectoryForTypeExisting($current_directory, $current_subdirectory_name);
        $is_target_subdirectory_existing  = !static::isSubdirectoryForTypeExisting($target_directory, $target_subdirectory_name);

        if( $is_current_subdirectory_existing ){
            return new Response('Current subdirectory does not exist.');
        }

        if( $is_target_subdirectory_existing ){
            return new Response('Target subdirectory does not exist.');
        }

        $current_subdirectory_path = static::getSubdirectoryPath($current_directory, $current_subdirectory_name);
        $target_subdirectory_path  = static::getSubdirectoryPath($target_directory, $target_subdirectory_name);

        try{
            Utils::copyFilesRecursively($current_subdirectory_path, $target_subdirectory_path);
        }catch(\Exception $e){
            return new Response('There was an error while moving files from one folder to another.');
        }

        return new Response('Data has been successfully moved to new directory');
    }

    /**
     * @param string $uploadType
     * @param bool $namesAsKeysAndValues
     * @return array
     * @throws \Exception
     */
    public static function getSubdirectoriesForUploadType(string $uploadType, $namesAsKeysAndValues = false)
    {
        $subdirectories = [];
        $finder         = new Finder();

        $targetDirectory = static::getTargetDirectoryForUploadType($uploadType);

        $finder->directories()->in($targetDirectory);

        foreach($finder as $directory){
            $subdirectories[] = $directory->getFilename();
        }

        if($namesAsKeysAndValues){
            $subdirectories = array_combine(
                array_values($subdirectories),
                array_values($subdirectories)
            );
        }

        return $subdirectories;
    }

    /**
     * @throws \Exception
     */
    public static function getSubdirectoriesForAllUploadTypes(){

        $subdirectories = [];

        foreach(static::UPLOAD_TYPES as $upload_type){
            $subdirectories = array_merge($subdirectories, static::getSubdirectoriesForUploadType($upload_type, true) );
        }

        return $subdirectories;
    }

    /**
     * @param string $uploadType
     * @return mixed
     * @throws \Exception
     */
    public static function getTargetDirectoryForUploadType(string $uploadType){

        switch ($uploadType) {
            case FileUploadController::TYPE_FILES:
                $targetDirectory = Env::getFilesUploadDir();
                break;
            case FileUploadController::TYPE_IMAGES:
                $targetDirectory = Env::getImagesUploadDir();
                break;
            default:
                throw new \Exception('This type is not allowed');
        }

        return $targetDirectory;
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectory_name
     * @return bool
     */
    public static function isSubdirectoryForTypeExisting(string $targetDirectory, string $subdirectory_name): bool {
        $subdirectory_path = static::getSubdirectoryPath($targetDirectory, $subdirectory_name);
        return file_exists($subdirectory_path);
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectory_name
     * @return string
     */
    public static function getSubdirectoryPath(string $targetDirectory, string $subdirectory_name){
        return $targetDirectory . '/' . $subdirectory_name;
    }

    /**
     * @param $subdirectories
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getUploadForm($subdirectories){
        return $this->createForm(UploadFormType::class, null, ['subdirectories' => $subdirectories]);
    }

    /**
     * @param Request $request
     * @param string $uploadType
     * @param FormInterface $form
     * @throws \Exception
     */
    private function handleFileUpload(Request $request, string $uploadType, FormInterface $form) {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            # This strange solution is needed for file upload case - datalistType is not perfect

            $modified_form_data = $request->request->get('upload_form');
            $original_form_data = $form->getData();

            $subdirectory       = $modified_form_data[static::SUBDIRECTORY_KEY];
            $uploadedFiles      = $original_form_data[static::FILE_KEY];

            foreach ($uploadedFiles as $uploadedFile) {
                $this->fileUploader->upload($uploadedFile, $uploadType, $subdirectory);
            }

        }

    }

}