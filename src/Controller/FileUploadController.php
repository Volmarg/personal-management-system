<?php


namespace App\Controller;

use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Form\UploadFormType;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';
    const FILE_KEY                      = 'file';
    const SUBDIRECTORY_KEY              = 'subdirectory';
    const TYPE_IMAGE                    = 'image';
    const TYPE_FILE                     = 'file';

    const KEY_SUBDIRECTORY_NEW_NAME     = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME = 'subdirectory_current_name';

    /**
     * @var FileUploader $fileUploader
     */
    private $fileUploader;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FileUploader $fileUploader, Application $app) {
        $this->fileUploader = $fileUploader;
        $this->app          = $app;
    }

    /**
     * @Route("/upload/{uploadType}", name="upload")
     * @param Request $request
     * @param string $uploadType
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function upload(Request $request, string $uploadType){

        $allowed_types  = [
            static:: TYPE_IMAGE,
            static:: TYPE_FILE
        ];

        if(!in_array($uploadType, $allowed_types)){
            throw new \Exception('This upload type is not allowed');
        }

        $subdirectories = static::getSubdirectoriesForUploadType($uploadType);

        $form = $this->getUploadForm($subdirectories);
        $this->handleFileUpload($request, $uploadType, $form);

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
     * @param string $uploadType
     * @param FormInterface $form
     * @throws \Exception
     */
    private function handleFileUpload(Request $request, string $uploadType, FormInterface $form) {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            # This strange solution is needed for file upload case - datalistType is not perfect

            $modified_form_data = $request->request->get('upload_form');
            $original_form_data = $form->getData();

            $subdirectory       = $modified_form_data[static::SUBDIRECTORY_KEY];
            $uploadedFiles      = $original_form_data[static::FILE_KEY];

            foreach ($uploadedFiles as $uploadedFile) {
                $this->fileUploader->upload($uploadedFile, $uploadType, $subdirectory);
            }

        }

    }


    /**
     * @Route("/upload/{upload_type}/rename-subdirectory", name="upload_rename_subdirectory", methods="POST")
     * @param string $uploadType
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function renameSubdirectory(string $upload_type, Request $request) {

        #TODO: check later how renaming will work with special chars etc

        if ( !$request->query->has(static::KEY_SUBDIRECTORY_NEW_NAME) ) {
            return new Response("Subdirectory new name is missing in request.");
        }

        if ( !$request->query->has(static::KEY_SUBDIRECTORY_CURRENT_NAME) ) {
            return new Response("Subdirectory current name is missing in request.");
        }

        $subdirectory_current_name  = $request->query->get(static::KEY_SUBDIRECTORY_CURRENT_NAME);
        $subdirectory_new_name      = $request->query->get(static::KEY_SUBDIRECTORY_NEW_NAME);

        if( $subdirectory_current_name === $subdirectory_new_name ){
            return new Response("You are trying to change folder name to the same that there already is - action aborted.");
        }

        $target_directory       = static::getTargetDirectoryForUploadType($upload_type);
        $subdirectory_exists    = static::isSubdirectoryForTypeExisting($target_directory, $subdirectory_current_name);

        if( !$subdirectory_exists ){
            return new Response("Subdirectory with this name does not exist!");
        }

        $subdirectory_with_new_name_exists = static::isSubdirectoryForTypeExisting($upload_type, $subdirectory_new_name);

        if( $subdirectory_with_new_name_exists ){
            return new Response(" Cannot change folder name! Folder with this name already exist.");
        }

        try{
            $old_folder_location = $target_directory.'/'.$subdirectory_current_name;
            $new_folder_location = $target_directory.'/'.$subdirectory_new_name;
            rename($old_folder_location, $new_folder_location);
        }catch(\Exception $e){
            return new Response('There was an error when renaming the folder!');
        }

        return new Response('Folder name has been successfully changed');

    }

    /**
     * @param $uploadType
     * @return array
     * @throws \Exception
     */
    public static function getSubdirectoriesForUploadType(string $uploadType)
    {
        $subdirectories = [];
        $finder         = new Finder();

        $targetDirectory = static::getTargetDirectoryForUploadType($uploadType);

        $finder->directories()->in($targetDirectory);

        foreach($finder as $directory){
            $subdirectories[] = $directory->getFilename();
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
            case FileUploadController::TYPE_FILE:
                $targetDirectory = Env::getFilesUploadDir();
                break;
            case FileUploadController::TYPE_IMAGE:
                $targetDirectory = Env::getImagesUploadDir();
                break;
            default:
                throw new \Exception('This type is not allowed');
        }

        return $targetDirectory;
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectory
     * @return bool
     */
    public static function isSubdirectoryForTypeExisting(string $targetDirectory, string $subdirectory): bool {
        return file_exists($targetDirectory . '/' . $subdirectory);
    }
}