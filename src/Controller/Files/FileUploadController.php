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
use App\Services\Translator;
use DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';

    const MODULE_UPLOAD_DIR_FOR_IMAGES  = 'images';
    const MODULE_UPLOAD_DIR_FOR_FILES   = 'files';

    const KEY_SUBDIRECTORY_NEW_NAME       = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME   = 'subdirectory_current_name';

    const KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR   = 'subdirectory_current_path_in_module_upload_dir';
    const KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR    = 'subdirectory_target_path_in_module_upload_dir';

    const KEY_SUBDIRECTORY_NAME         = 'subdirectory_name';

    const KEY_UPLOAD_MODULE_DIR         = 'upload_module_dir';

    const KEY_MAIN_FOLDER               = 'Main folder';

    const MODULES_UPLOAD_DIRS = [
        self::MODULE_UPLOAD_DIR_FOR_IMAGES => self::MODULE_UPLOAD_DIR_FOR_IMAGES,
        self::MODULE_UPLOAD_DIR_FOR_FILES  => self::MODULE_UPLOAD_DIR_FOR_FILES
    ];

    const MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES = [
        MyImagesController::MODULE_NAME => self::MODULE_UPLOAD_DIR_FOR_IMAGES,
        MyFilesController::MODULE_NAME  => self::MODULE_UPLOAD_DIR_FOR_FILES
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

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        return $this->renderTemplate(true);
    }

    /**
     * @Route("/upload/send", name="upload_send")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function sendData(Request $request){
        $this->handleFileUpload($request);

        $referer_url     = $request->server->get('HTTP_REFERER');
        $upload_page_url = $this->generateUrl('upload');

        if( $referer_url === $upload_page_url || empty($referer_url) ) {
            return $this->renderTemplate(false);
        }

        return $this->redirect($referer_url);
    }

    /**
     * @param $ajax_render
     * @return Response
     * @throws \Exception
     */
    private function renderTemplate($ajax_render)
    {
        $upload_max_filesize        = preg_replace("/[^0-9]/","", ini_get('upload_max_filesize'));
        $post_max_size              = preg_replace("/[^0-9]/","", ini_get('post_max_size'));
        $max_allowed_files_count    = ini_get('max_file_uploads');

        $max_upload_size_mb  = ( $post_max_size < $upload_max_filesize ? $post_max_size : $upload_max_filesize);

        $form = $this->getUploadForm();

        $data = [
            'ajax_render'               => $ajax_render,
            'form'                      => $form->createView(),
            'max_upload_size_mb'        => $max_upload_size_mb,
            'max_allowed_files_count'   => $max_allowed_files_count
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);

    }

    /**
     * @param string $upload_module_dir
     * @return mixed
     * @throws \Exception
     */
    public static function getTargetDirectoryForUploadModuleDir(string $upload_module_dir){
        $translator = new Translator();

        switch ($upload_module_dir) {
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $targetDirectory = Env::getFilesUploadDir();
                break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $targetDirectory = Env::getImagesUploadDir();
                break;
            default:
                $message  = $translator->translate('responses.upload.uploadDirNotSupported');
                throw new \Exception($message);
        }

        return $targetDirectory;
    }

    /**
     * @param string $target_directory
     * @param string $subdirectory_name
     * @return bool
     */
    public static function isSubdirectoryForModuleDirExisting(string $target_directory, string $subdirectory_name): bool {
        $subdirectory_path = static::getSubdirectoryPath($target_directory, $subdirectory_name);
        return file_exists($subdirectory_path);
    }

    /**
     * @param string $target_directory
     * @param string $subdirectory_name
     * @return string
     */
    public static function getSubdirectoryPath(string $target_directory, string $subdirectory_name){
        return $target_directory . '/' . $subdirectory_name;
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

        $message  = $this->app->translator->translate('responses.upload.noFilesWereUploaded');
        $response = new Response($message);

        if ($form->isSubmitted() && $form->isValid()) {

            $form_data = $form->getData();

            $subdirectory       = $form_data[DirectoriesHandler::SUBDIRECTORY_KEY];
            $upload_module_dir  = $form_data[static::KEY_UPLOAD_MODULE_DIR];
            $uploaded_files     = $form_data[FilesHandler::FILE_KEY];
            $max_file_uploads   = (int)ini_get('max_file_uploads');

            foreach ($uploaded_files as $uploadedFile) {
                $uploaded_files_count = count($uploaded_files);

                if( $uploaded_files_count > $max_file_uploads){
                    $message  = $this->app->translator->translate('responses.upload.tryingToUploadMoreFilesThanAllowedTo');
                    $response = new Response($message);
                    break;
                }

                $response = $this->fileUploader->upload($uploadedFile, $request, $upload_module_dir, $subdirectory);
            }

            $flash_type  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flash_type, $message);
        }

    }

    /**
     * @param bool $grouped_by_module_upload_dirs
     * @param bool $include_main_folder
     * @return array
     * @throws \Exception
     */
    public static function getFoldersTreesForAllUploadModulesDirs($grouped_by_module_upload_dirs = false, $include_main_folder = false){

        $subdirectories = [];

        if( !$grouped_by_module_upload_dirs ){
            foreach(static::MODULES_UPLOAD_DIRS as $module_upload_dir){
                $subdirectories = array_merge($subdirectories, static::getFoldersTreesForUploadModuleDir($module_upload_dir, $include_main_folder) );
            }
        }else{
            foreach(static::MODULES_UPLOAD_DIRS as $module_upload_dir){
                $subdirectories[$module_upload_dir] = static::getFoldersTreesForUploadModuleDir($module_upload_dir, $include_main_folder);
            }
        }

        return $subdirectories;
    }

    /**
     * @param string $upload_module_dir
     * @param bool $include_main_folder
     * @return array|false
     * @throws \Exception
     */
    public static function getFoldersTreesForUploadModuleDir(string $upload_module_dir, $include_main_folder = false)
    {
        $target_directory_for_module_upload_dir = static::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $folders_trees                          = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory_for_module_upload_dir ), true );

        if( $include_main_folder ){
            $subdirectories[static::KEY_MAIN_FOLDER] = "";
        }

        return $folders_trees;
    }

}