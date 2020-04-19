<?php


namespace App\Action\Files;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesUploadSettingsAction extends AbstractController {

    const TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS = 'modules/common/files-upload-settings.html.twig';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    public function __construct(
        Controllers         $controllers,
        Application         $app,
        DirectoriesHandler  $directories_handler,
        FilesHandler        $files_handler
    ) {
        $this->app                 = $app;
        $this->controllers         = $controllers;
        $this->directories_handler = $directories_handler;
        $this->files_handler       = $files_handler;
    }

    /**
     * @Route("upload/settings", name="upload_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsPage(false, $request);
        }

        $template_content = $this->renderSettingsPage(true, $request)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    private function renderSettingsPage(bool $ajax_render, Request $request){

        $rename_form    = $this->app->forms->renameSubdirectoryForm();
        $rename_form->handleRequest($request);

        $copy_data_form = $this->app->forms->copyUploadSubdirectoryDataForm();
        $copy_data_form->handleRequest($request);

        $create_subdir_form = $this->app->forms->createSubdirectoryForm();

        $this->handleForms($rename_form, $copy_data_form);

        $menu_node_modules_names_to_reload = [
            ModulesController::MODULE_NAME_IMAGES,
            ModulesController::MODULE_NAME_FILES,
        ];

        $data = [
            'ajax_render'                       => $ajax_render,
            'rename_form'                       => $rename_form->createView(),
            'copy_data_form'                    => $copy_data_form->createView(),
            'create_subdir_form'                => $create_subdir_form->createView(),
            "menu_node_modules_names_to_reload" => Utils::escapedDoubleQuoteJsonEncode($menu_node_modules_names_to_reload),
        ];

        return $this->render(static::TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS, $data);
    }

    /**
     * @param FormInterface $rename_form
     * @param FormInterface $copy_data_form
     * @throws Exception
     */
    private function handleForms(FormInterface $rename_form, FormInterface $copy_data_form){

        if($rename_form->isSubmitted() && $rename_form->isValid()) {
            $form_data      = $rename_form->getData();
            $new_name       = $form_data[FileUploadController::KEY_SUBDIRECTORY_NEW_NAME];
            $upload_module_dir    = $form_data[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $current_directory_path_in_module_upload_dir = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR];

            $response = $this->controllers->getFilesUploadSettingsController()->renameSubdirectory($upload_module_dir, $current_directory_path_in_module_upload_dir, $new_name);
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