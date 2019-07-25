<?php

namespace App\Controller\Modules\Images;

use App\Controller\Files\FileUploadController;
use App\Form\Files\UploadSubdirectoryRenameType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesSettingsController extends AbstractController {

    const TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS = 'modules/common/files-upload-settings.html.twig';

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

        #TODO: maybe I can use all this logic generally for upload not only for images

        switch($upload_type){
            case FileUploadController::TYPE_FILES:

            break;

            case FileUploadController::TYPE_IMAGES:

            break;
        }

        $subdirectories = FileUploadController::getSubdirectoriesForUploadType($upload_type, true);
        $form           = $this->getRenameSubdirectoryForm($upload_type, $subdirectories);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $form_data      = $form->getData();
            $current_name   = $form_data[FileUploadController::KEY_SUBDIRECTORY_CURRENT_NAME];
            $new_name       = $form_data[FileUploadController::KEY_SUBDIRECTORY_NEW_NAME];

            # TODO: handle exception or make some messaging?
            $response = $this->renameSubdirectory($upload_type, $current_name, $new_name);
        }

        $data = [
            'ajax_render'   => false,
            'form'          => $form->createView()
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
            UploadSubdirectoryRenameType::OPTION_UPLOAD_TYPE    => $upload_type,
            UploadSubdirectoryRenameType::OPTION_SUBDIRECTORIES => $subdirectories,
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

}
