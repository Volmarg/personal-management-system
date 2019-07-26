<?php

namespace App\Controller\Modules\Images;

use App\Controller\Files\FileUploadController;
use App\Form\Files\UploadSubdirectoryMoveDataType;
use App\Form\Files\UploadSubdirectoryRemoveType;
use App\Form\Files\UploadSubdirectoryRenameType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesSettingsController extends AbstractController {

    const TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS = 'modules/common/files-upload-settings.html.twig';
    #TODO: I can most likely reuse this logic also for other uploadTypes
    #TODO: Add module folder Uploads - there throw all separated and common logic

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FileUploadController $file_upload_controller
     */
    private $file_upload_controller;

    public function __construct(FileUploadController $file_upload_controller) {
        $this->finder                 = new Finder();
        $this->file_upload_controller = $file_upload_controller;
    }

    /**
     * @Route("my-{upload_type}/settings", name="modules_my_images_settings", requirements={
     *     "upload_type": "images|files"
     *     })
     * @param string $upload_type
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function displaySettings(string $upload_type, Request $request) {

        $subdirectories                   = FileUploadController::getSubdirectoriesForUploadType($upload_type, true);
        $all_subdirectories_for_all_types = FileUploadController::getSubdirectoriesForAllUploadTypes();

        $rename_form    = $this->getRenameSubdirectoryForm($upload_type, $subdirectories);
        $rename_form->handleRequest($request);

        $remove_form    = $this->getRemoveSubdirectoryForm($upload_type, $subdirectories);
        $remove_form->handleRequest($request);

        $move_data_form = $this->getMoveUploadSubdirectoryDataForm($all_subdirectories_for_all_types);
        $move_data_form->handleRequest($request);

        $this->handleForms($upload_type, $rename_form, $remove_form, $move_data_form);

        $data = [
            'ajax_render'       => false,
            'rename_form'       => $rename_form->createView(),
            'remove_form'       => $remove_form->createView(),
            'move_data_form'    => $move_data_form->createView()
        ];

        return $this->render(static::TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS, $data);
    }

    /**
     * @param string $upload_type
     * @param array $subdirectories
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getRenameSubdirectoryForm(string $upload_type, array $subdirectories){

        $form = $this->createForm(UploadSubdirectoryRenameType::class, null, [
            UploadSubdirectoryRenameType::OPTION_UPLOAD_TYPE  => $upload_type,
            UploadSubdirectoryRenameType::OPTION_SUBDIRECTORY => $subdirectories,
        ]);

        return $form;
    }

    /**
     * @param string $upload_type
     * @param array $subdirectories
     * @return FormInterface
     */
    public function getRemoveSubdirectoryForm(string $upload_type, array $subdirectories){

        $form = $this->createForm(UploadSubdirectoryRemoveType::class, null, [
            UploadSubdirectoryRemoveType::OPTION_UPLOAD_TYPE    => $upload_type,
            UploadSubdirectoryRemoveType::OPTION_SUBDIRECTORIES => $subdirectories,
        ]);

        return $form;
    }

    public function getMoveUploadSubdirectoryDataForm(array $all_subdirectories_for_all_types){

        $form = $this->createForm(UploadSubdirectoryMoveDataType::class, null, [
            FileUploadController::KEY_CURRENT_SUBDIRECTORY_NAME   => $all_subdirectories_for_all_types,
            FileUploadController::KEY_TARGET_SUBDIRECTORY_NAME    => $all_subdirectories_for_all_types
        ]);

        return $form;
    }

    /**
     * @param string $upload_type
     * @param string $current_name
     * @param string $new_name
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function renameSubdirectory(string $upload_type, string $current_name, string $new_name){
        $response = $this->file_upload_controller->renameSubdirectory($upload_type, $current_name, $new_name);
        return $response;
    }

    /**
     * @param string $upload_type
     * @param FormInterface $rename_form
     * @param FormInterface $remove_form
     * @param FormInterface $move_data_form
     * @throws \Exception
     */
    private function handleForms(string $upload_type, FormInterface $rename_form, FormInterface $remove_form, FormInterface $move_data_form){
        # TODO: handle exception or make some messaging?

        if($rename_form->isSubmitted() && $rename_form->isValid()) {
            $form_data      = $rename_form->getData();
            $current_name   = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME];
            $new_name       = $form_data[FileUploadController::KEY_SUBDIRECTORY_NEW_NAME];

            $response = $this->renameSubdirectory($upload_type, $current_name, $new_name);
        }

        if($remove_form->isSubmitted() && $remove_form->isValid()) {
            $form_data          = $remove_form->getData();
            $subdirectory_name  = $form_data[FileUploadController::KEY_SUBDIRECTORY_NAME];

            $response = $this->file_upload_controller->removeFolder($upload_type, $subdirectory_name);
        }

        if($move_data_form->isSubmitted() && $move_data_form->isValid()) {
            $form_data                          = $move_data_form->getData();
            $current_upload_type                = $form_data[FileUploadController::KEY_CURRENT_UPLOAD_TYPE];
            $target_upload_type                 = $form_data[FileUploadController::KEY_TARGET_UPLOAD_TYPE];
            $current_subdirectory_name          = $form_data[FileUploadController::KEY_CURRENT_SUBDIRECTORY_NAME];
            $target_subdirectory_name           = $form_data[FileUploadController::KEY_TARGET_SUBDIRECTORY_NAME];
            $remove_current_folder              = $form_data[UploadSubdirectoryMoveDataType::FIELD_REMOVE_CURRENT_FOLDER];

            $response = $this->file_upload_controller->copyAndRemoveData(
                $current_upload_type, $target_upload_type, $current_subdirectory_name, $target_subdirectory_name, $remove_current_folder
            );
        }



    }

}
