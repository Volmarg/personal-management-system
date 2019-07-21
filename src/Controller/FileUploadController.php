<?php


namespace App\Controller;

use App\Form\UploadFormType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE = 'core/upload/upload-page.html.twig';
    const FILE_KEY                  = 'file';
    const TYPE_IMAGE                = 'image';
    const TYPE_FILE                 = 'file';

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    public function __construct(FileUploader $fileUploader) {
        $this->fileUploader = $fileUploader;
    }

    /**
     * @Route("/upload/{type}", name="upload")
     * @param Request $request
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function upload(Request $request, string $type){

        $allowed_types = [
            static:: TYPE_IMAGE,
            static:: TYPE_FILE
        ];

        if(!in_array($type, $allowed_types)){
            throw new \Exception('This upload type is not allowed');
        }

        $data = [
            'ajax_render'   => false,
            'form'          => $this->getUploadForm()->createView()
        ];

        $this->handleFileUpload($request, $type);

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getUploadForm(){
        return $this->createForm(UploadFormType::class);
    }

    /**
     * @param Request $request
     * @param string $type
     */
    private function handleFileUpload(Request $request, string $type) {
        $form           = $this->getUploadForm();
        $form->handleRequest($request);
        $form_data      = $form->getData();
        $uploadedFiles  = $form_data[static::FILE_KEY];

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($uploadedFiles as $uploadedFile) {
                $this->fileUploader->upload($uploadedFile, $type);
            }

        }

    }
}