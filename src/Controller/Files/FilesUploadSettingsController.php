<?php


namespace App\Controller\Files;

use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Form\Files\UploadSubdirectoryMoveDataType;
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

    public function __construct(FileUploadController $file_upload_controller, DirectoriesHandler $directories_handler, FilesHandler $files_handler) {
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

        //TODO: moving entire data must use main, and with this i need to block removed all if I select main + block it on backend
        //$all_subdirectories_for_all_types_with_main_folders = FileUploadController::getSubdirectoriesForAllUploadTypes(true, true);

        $rename_form    = $this->getRenameSubdirectoryForm();
        $rename_form->handleRequest($request);

        $move_data_form = $this->getMoveUploadSubdirectoryDataForm();
        $move_data_form->handleRequest($request);

        $create_subdir_form = $this->getCreateSubdirectoryForm();
        $create_subdir_form->handleRequest($request);

        $this->handleForms($rename_form, $move_data_form, $create_subdir_form);

        $data = [
            'ajax_render'           => false,
            'rename_form'           => $rename_form->createView(),
            'move_data_form'        => $move_data_form->createView(),
            'create_subdir_form'    => $create_subdir_form->createView()
        ];

        return $this->render(static::TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getRenameSubdirectoryForm(){
        $form = $this->createForm(UploadSubdirectoryRenameType::class);

        return $form;
    }

    /**
     * @return FormInterface
     */
    public function getMoveUploadSubdirectoryDataForm() {
        $form = $this->createForm(UploadSubdirectoryMoveDataType::class);

        return $form;
    }

    public function getCreateSubdirectoryForm() {

        $form = $this->createForm(UploadSubdirectoryCreateType::class);

        return $form;
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
     * @param FormInterface $remove_form
     * @param FormInterface $move_data_form
     * @param FormInterface $create_subdir_form
     * @throws \Exception
     */
    private function handleForms(FormInterface $rename_form, FormInterface $move_data_form, FormInterface $create_subdir_form){

        if($rename_form->isSubmitted() && $rename_form->isValid()) {
            $form_data      = $rename_form->getData();
            $new_name       = $form_data[FileUploadController::KEY_SUBDIRECTORY_NEW_NAME];
            $upload_module_dir    = $form_data[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $current_directory_path_in_module_upload_dir = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR];

            $response = $this->renameSubdirectory($upload_module_dir, $current_directory_path_in_module_upload_dir, $new_name);
        }

        if($move_data_form->isSubmitted() && $move_data_form->isValid()) {
            $form_data                          = $move_data_form->getData();
            $current_upload_module_dir          = $form_data[FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR];
            $target_upload_module_dir           = $form_data[FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR];

            $current_directory_path_in_module_upload_dir  = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR];
            $target_directory_path_in_module_upload_dir   = $form_data[FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR];

            $response = $this->files_handler->copyData(
                $current_upload_module_dir, $target_upload_module_dir, $current_directory_path_in_module_upload_dir, $target_directory_path_in_module_upload_dir
            );
        }

        if($create_subdir_form->isSubmitted() && $create_subdir_form->isValid()) {
            $form_data          = $create_subdir_form->getData();
            $subdirectory_name  = $form_data[FileUploadController::KEY_SUBDIRECTORY_NAME];
            $upload_module_dir  = $form_data[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $target_directory_path_in_module_upload_dir = $form_data[FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR];

            $response = $this->directories_handler->createFolder($upload_module_dir, $subdirectory_name, $target_directory_path_in_module_upload_dir);
        }

        if( isset($response) ){
            $flashType  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flashType, $message);
        }

    }

}