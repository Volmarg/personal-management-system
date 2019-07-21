<?php


namespace App\Controller;

use App\Form\UploadFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE = 'core/upload/upload-page.html.twig';

    /**
     * @Route("/upload/images", name="upload_images")
     */
    public function uploadImages(){

    }

    /**
     * @Route("/upload/files", name="upload_files")
     */
    public function uploadFiles(){

        $data = [
            'ajax_render'   => false,
            'form'          => $this->getUploadForm()->createView()
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getUploadForm(){
        return $this->createForm(UploadFormType::class);
    }
}