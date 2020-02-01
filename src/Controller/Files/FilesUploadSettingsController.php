<?php


namespace App\Controller\Files;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Form\Files\UploadSubdirectoryRemoveType;
use App\Form\Files\UploadSubdirectoryRenameType;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesUploadSettingsController extends AbstractController {


    const TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS = 'modules/common/files-upload-settings.html.twig';

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FileUploadController $file_upload_controller
     */
    private $file_upload_controller;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FileUploadController $file_upload_controller, DirectoriesHandler $directories_handler, FilesHandler $files_handler, Application $app) {
        $this->app                    = $app;
        $this->finder                 = new Finder();
        $this->file_upload_controller = $file_upload_controller;
        $this->directories_handler    = $directories_handler;
        $this->files_handler          = $files_handler;
    }

    /**
     * @Route("upload/settings", name="upload_settings")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function displaySettings(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsPage(false, $request);
        }

        $template_content  = $this->renderSettingsPage(true, $request)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    private function renderSettingsPage(bool $ajax_render, Request $request){

        $rename_form    = $this->app->forms->renameSubdirectoryForm();
        $rename_form->handleRequest($request);

        $copy_data_form = $this->app->forms->copyUploadSubdirectoryDataForm();
        $copy_data_form->handleRequest($request);

        $create_subdir_form = $this->app->forms->createSubdirectoryForm();

        $this->handleForms($rename_form, $copy_data_form);

        $data = [
            'ajax_render'           => $ajax_render,
            'rename_form'           => $rename_form->createView(),
            'copy_data_form'        => $copy_data_form->createView(),
            'create_subdir_form'    => $create_subdir_form->createView()
        ];

        return $this->render(static::TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS, $data);
    }

    /**
     * @param string $upload_type
     * @param string $current_directory_path_in_module_upload_dir
     * @param string $new_name
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(?string $upload_type, ?string $current_directory_path_in_module_upload_dir, ?string $new_name){
        $response = $this->directories_handler->renameSubdirectory($upload_type, $current_directory_path_in_module_upload_dir, $new_name);
        return $response;
    }

    /**
     * @param FormInterface $rename_form
     * @param FormInterface $copy_data_form
     * @throws \Exception
     */
    private function handleForms(FormInterface $rename_form, FormInterface $copy_data_form){

        if($rename_form->isSubmitted() && $rename_form->isValid()) {
            $form_data      = $rename_form->getData();
            $new_name       = $form_data[FileUploadController::KEY_SUBDIRECTORY_NEW_NAME];
            $upload_module_dir    = $form_data[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $current_directory_path_in_module_upload_dir = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR];

            $response = $this->renameSubdirectory($upload_module_dir, $current_directory_path_in_module_upload_dir, $new_name);
        }

        if($copy_data_form->isSubmitted() && $copy_data_form->isValid()) {
            $form_data                          = $copy_data_form->getData();
            $current_upload_module_dir          = $form_data[FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR];
            $target_upload_module_dir           = $form_data[FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR];

            $current_directory_path_in_module_upload_dir  = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR];
            $target_directory_path_in_module_upload_dir   = $form_data[FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR];

            if( $form_data[UploadSubdirectoryCopyDataType::KEY_MOVE_FOLDER] ){

                $upload_dirs         = Env::getUploadDirs();
                $current_folder_path = $current_directory_path_in_module_upload_dir;
                $target_folder_path  = $target_directory_path_in_module_upload_dir;

                //if not main folder then add upload dir
                if( !in_array($current_directory_path_in_module_upload_dir, $upload_dirs) ){
                    $current_folder_path =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $current_upload_module_dir . DIRECTORY_SEPARATOR . $current_directory_path_in_module_upload_dir;
                }

                //if not main folder then add upload dir
                if( !in_array($target_directory_path_in_module_upload_dir, $upload_dirs) ){
                    $target_folder_path  =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $target_upload_module_dir . DIRECTORY_SEPARATOR . $target_directory_path_in_module_upload_dir;
                }

                $response = $this->directories_handler->moveDirectory($current_folder_path, $target_folder_path);
            }else{
                $response = $this->files_handler->copyData(
                    $current_upload_module_dir, $target_upload_module_dir, $current_directory_path_in_module_upload_dir, $target_directory_path_in_module_upload_dir
                );
            }

        }

        if( isset($response) ){
            $flashType  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flashType, $message);
        }

    }

}