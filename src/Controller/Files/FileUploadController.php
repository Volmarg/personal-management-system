<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Controller\Utils\Utils;
use App\Form\UploadFormType;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use App\Services\FileUploader;
use DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';

    const TYPE_IMAGES                   = 'images'; #TODO: rename MODULE_UPLOAD_DIR_NAME_FOR_IMAGES ?
    const TYPE_FILES                    = 'files';

    const KEY_SUBDIRECTORY_NEW_NAME       = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME   = 'subdirectory_current_name';

    const KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR   = 'subdirectory_current_path_in_module_upload_dir';
    const KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR    = 'subdirectory_target_path_in_module_upload_dir';

    const KEY_SUBDIRECTORY_NAME         = 'subdirectory_name';

    const KEY_UPLOAD_TYPE               = 'upload_type';

    const DEFAULT_KEY_IS_MISSING        = 'Required key is missing in request';

    const KEY_MAIN_FOLDER               = 'Main folder';

    const UPLOAD_TYPES = [
        self::TYPE_IMAGES => self::TYPE_IMAGES,
        self::TYPE_FILES  => self::TYPE_FILES
    ];

    const UPLOAD_BASED_MODULES = [
        MyImagesController::MODULE_NAME => self::TYPE_IMAGES,
        MyFilesController::MODULE_NAME  => self::TYPE_FILES
    ];

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

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

    public function __construct(FileUploader $fileUploader, FilesHandler $filesHandler, DirectoriesHandler $directoriesHandler, Application $app) {
        $this->fileUploader         = $fileUploader;
        $this->app                  = $app;
        $this->filesHandler         = $filesHandler;
        $this->directoriesHandler   = $directoriesHandler;
    }

    /**
     * @Route("/upload/", name="upload")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function displayUploadPage(Request $request) {
        $this->sendData($request);
        return $this->renderTemplate(false);
    }

    /**
     * @Route("/upload/send", name="upload_send")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendData(Request $request){
        $this->handleFileUpload($request);

        $data = [
            'template' => $this->renderTemplate(true)->getContent()
        ];

        return new JsonResponse($data);
    }

    /**
     * @param $ajax_render
     * @return Response
     * @throws \Exception
     */
    private function renderTemplate($ajax_render)
    {
        $upload_max_filesize = preg_replace("/[^0-9]/","",ini_get('upload_max_filesize'));
        $post_max_size       = preg_replace("/[^0-9]/","",ini_get('post_max_size'));

        $max_upload_size_mb  = ( $post_max_size < $upload_max_filesize ? $post_max_size : $upload_max_filesize);

        $form = $this->getUploadForm();

        $data = [
            'ajax_render'           => $ajax_render,
            'form'                  => $form->createView(),
            'max_upload_size_mb'    => $max_upload_size_mb
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);

    }

    /**
     * @see buildFoldersTreeForDirectory
     * @param string $uploadType
     * @param bool $names_as_keys_and_values
     * @param bool $include_main_folder
     * @return array
     * @throws \Exception
     */
    public static function getSubdirectoriesForUploadType(string $uploadType, $names_as_keys_and_values = false, $include_main_folder = false)
    {
        $subdirectories = [];
        $finder         = new Finder();

        $targetDirectory = static::getTargetDirectoryForUploadType($uploadType);

        $finder->directories()->in($targetDirectory);

        foreach($finder as $directory){
            $subdirectories[] = $directory->getFilename();
        }

        if($names_as_keys_and_values){
            $subdirectories = array_combine(
                array_values($subdirectories),
                array_values($subdirectories)
            );
        }

        if( $include_main_folder ){
            $subdirectories[static::KEY_MAIN_FOLDER] = "";
        }

        return $subdirectories;
    }

    /**
     * @param bool $grouped_by_types
     * @param bool $include_main_folder
     * @return array
     * @throws \Exception
     */
    public static function getSubdirectoriesForAllUploadTypes($grouped_by_types = false, $include_main_folder = false){

        $subdirectories = [];

        if( !$grouped_by_types ){
            foreach(static::UPLOAD_TYPES as $upload_type){
                $subdirectories = array_merge($subdirectories, static::getSubdirectoriesForUploadType($upload_type, true, $include_main_folder) );
            }
        }else{
            foreach(static::UPLOAD_TYPES as $upload_type){
                $subdirectories[$upload_type] = static::getSubdirectoriesForUploadType($upload_type, true, $include_main_folder);
            }
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

    public static function getUploadTypeForTargetDirectory(string $target_directory) {

        switch ($target_directory) {
            case Env::getImagesUploadDir():
                $target_module = MyImagesController::TARGET_TYPE;
                break;
            case Env::getFilesUploadDir():
                $target_module = MyFilesController::TARGET_TYPE;
                break;
            default:
                throw new \Exception('This target_directory is not allowed');
        }

        return $target_module;
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
     * @return \Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    private function getUploadForm(){
        return $this->createForm(UploadFormType::class, null, []);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    private function handleFileUpload(Request $request) {
        $form = $this->getUploadForm();
        $form->handleRequest($request);

        $response = new Response('No files were uploaded');

        if ($form->isSubmitted() && $form->isValid()) {

            $form_data = $form->getData();

            $subdirectory       = $form_data[DirectoriesHandler::SUBDIRECTORY_KEY];
            $upload_type        = $form_data[static::KEY_UPLOAD_TYPE];
            $uploadedFiles      = $form_data[FilesHandler::FILE_KEY];

            foreach ($uploadedFiles as $uploadedFile) {
                $response = $this->fileUploader->upload($uploadedFile, $request, $upload_type, $subdirectory);
            }

            $flashType  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flashType, $message);
        }

    }

    /**
     * @param bool $grouped_by_types
     * @param bool $include_main_folder
     * @return array
     * @throws \Exception
     */
    public static function getFoldersTreesForAllUploadTypes($grouped_by_types = false, $include_main_folder = false){

        $subdirectories = [];

        if( !$grouped_by_types ){
            foreach(static::UPLOAD_TYPES as $upload_type){
                $subdirectories = array_merge($subdirectories, static::getFoldersTreesForUploadType($upload_type, $include_main_folder) );
            }
        }else{
            foreach(static::UPLOAD_TYPES as $upload_type){
                $subdirectories[$upload_type] = static::getFoldersTreesForUploadType($upload_type, $include_main_folder);
            }
        }

        return $subdirectories;
    }

    /**
     * @param string $uploadType
     * @param bool $include_main_folder
     * @return array|false
     * @throws \Exception
     */
    public static function getFoldersTreesForUploadType(string $uploadType, $include_main_folder = false)
    {
        $target_directory = static::getTargetDirectoryForUploadType($uploadType);
        $folders_trees    = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory), true );

        if( $include_main_folder ){
            $subdirectories[static::KEY_MAIN_FOLDER] = "";
        }

        return $folders_trees;
    }

}