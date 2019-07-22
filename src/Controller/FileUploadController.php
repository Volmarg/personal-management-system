<?php


namespace App\Controller;

use App\Controller\Utils\Application;
use App\Form\UploadFormType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE = 'core/upload/upload-page.html.twig';
    const FILE_KEY                  = 'file';
    const SUBDIRECTORY_KEY          = 'subdirectory';
    const TYPE_IMAGE                = 'image';
    const TYPE_FILE                 = 'file';

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FileUploader $fileUploader, Application $app) {
        $this->fileUploader = $fileUploader;
        $this->finder       = new Finder();
        $this->app          = $app;
    }

    /**
     * @Route("/upload/{type}", name="upload")
     * @param Request $request
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function upload(Request $request, string $type){

        $subdirectories = [];
        $allowed_types  = [
            static:: TYPE_IMAGE,
            static:: TYPE_FILE
        ];

        if(!in_array($type, $allowed_types)){
            throw new \Exception('This upload type is not allowed');
        }

        switch($type){
            case FileUploadController::TYPE_FILE:
                $targetDirectory = EnvController::getFilesUploadDir();
                break;
            case FileUploadController::TYPE_IMAGE:
                $targetDirectory = EnvController::getImagesUploadDir();
                break;
            default:
                throw new \Exception('This type is not allowed');
        }

        $this->finder->directories()->in($targetDirectory);

        foreach($this->finder as $directory){
            $subdirectories[]   = $directory->getFilename();
        }

        $form = $this->getUploadForm($subdirectories);
        $this->handleFileUpload($request, $type, $form);

        $data = [
            'ajax_render'       => false,
            'form'              => $form->createView()
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);
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
     * @param string $type
     * @param FormInterface $form
     * @throws \Exception
     */
    private function handleFileUpload(Request $request, string $type, FormInterface $form) {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            # This strange solution is needed for file upload case - datalistType is not perfect

            $modified_form_data = $request->request->get('upload_form');
            $original_form_data = $form->getData();

            $subdirectory       = $modified_form_data[static::SUBDIRECTORY_KEY];
            $uploadedFiles      = $original_form_data[static::FILE_KEY];

            foreach ($uploadedFiles as $uploadedFile) {
                $this->fileUploader->upload($uploadedFile, $type, $subdirectory);
            }

        }

    }
}