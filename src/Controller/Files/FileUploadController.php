<?php


namespace App\Controller\Files;

use App\Controller\Utils\Application;
use App\Controller\Utils\Env;
use App\Form\UploadFormType;
use App\Services\DirectoriesHandler;
use App\Services\FilesHandler;
use App\Services\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AbstractController {

    # TODO: add moving subdirectories to other folder
        # But do it by copying and removing if copying was done
        # If copying was not done correctly then warn user about it - he should remove this on his own
        # maybe moving progress?
    # TODO: add moving content of one folder to another
    # TODO: handle case when user tries to enter non existing folder via url
    # TODO: add handling folder creation in settings
    # TODO: add logging for most crucial parts, like removing/copying data
    # TODO: rethink if all the advanced logic of moving files/removing dirs shouldn't bee in some case of FilesAndFoldersService?
    #   Or both: FilesService and FoldersService
    #   This is no longer part of upload, so files service would be best
    # TODO: recheck all consts in forms as well
    # TODO: most likely there will be new module required Upload because the uploading mechnisms are no loner just part of images/files uploads
    #   with this I will need to rethink putting MyImages and MyFiles into Upload module - if I split all the logic

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';

    const TYPE_IMAGES                   = 'images';
    const TYPE_FILES                    = 'files';

    const KEY_SUBDIRECTORY_NEW_NAME     = 'subdirectory_new_name';
    const KEY_SUBDIRECTORY_CURRENT_NAME = 'subdirectory_current_name';

    const KEY_SUBDIRECTORY_NAME         = 'subdirectory_name';

    const UPLOAD_TYPES = [
        self::TYPE_IMAGES => self::TYPE_IMAGES,
        self::TYPE_FILES  => self::TYPE_FILES
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
     * @Route("/upload/{upload_type}", name="upload")
     * @param Request $request
     * @param string $upload_type
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function upload(Request $request, string $upload_type){

        $allowed_types  = [
            static:: TYPE_IMAGES,
            static:: TYPE_FILES
        ];

        if(!in_array($upload_type, $allowed_types)){
            throw new \Exception('This upload type is not allowed');
        }

        $subdirectories = static::getSubdirectoriesForUploadType($upload_type);

        $form = $this->getUploadForm($subdirectories);
        $this->handleFileUpload($request, $upload_type, $form);

        $data = [
            'ajax_render'       => false,
            'form'              => $form->createView()
        ];

        return $this->render(static::UPLOAD_PAGE_TWIG_TEMPLATE, $data);
    }

    /**
     * @param string $uploadType
     * @param bool $namesAsKeysAndValues
     * @return array
     * @throws \Exception
     */
    public static function getSubdirectoriesForUploadType(string $uploadType, $namesAsKeysAndValues = false)
    {
        $subdirectories = [];
        $finder         = new Finder();

        $targetDirectory = static::getTargetDirectoryForUploadType($uploadType);

        $finder->directories()->in($targetDirectory);

        foreach($finder as $directory){
            $subdirectories[] = $directory->getFilename();
        }

        if($namesAsKeysAndValues){
            $subdirectories = array_combine(
                array_values($subdirectories),
                array_values($subdirectories)
            );
        }

        return $subdirectories;
    }

    /**
     * @throws \Exception
     */
    public static function getSubdirectoriesForAllUploadTypes(){

        $subdirectories = [];

        foreach(static::UPLOAD_TYPES as $upload_type){
            $subdirectories = array_merge($subdirectories, static::getSubdirectoriesForUploadType($upload_type, true) );
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
            case FileUploadController::TYPE_FILES:
                $targetDirectory = Env::getFilesUploadDir();
                break;
            case FileUploadController::TYPE_IMAGES:
                $targetDirectory = Env::getImagesUploadDir();
                break;
            default:
                throw new \Exception('This type is not allowed');
        }

        return $targetDirectory;
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectory_name
     * @return bool
     */
    public static function isSubdirectoryForTypeExisting(string $targetDirectory, string $subdirectory_name): bool {
        $subdirectory_path = static::getSubdirectoryPath($targetDirectory, $subdirectory_name);
        return file_exists($subdirectory_path);
    }

    /**
     * @param string $targetDirectory
     * @param string $subdirectory_name
     * @return string
     */
    public static function getSubdirectoryPath(string $targetDirectory, string $subdirectory_name){
        return $targetDirectory . '/' . $subdirectory_name;
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

            $subdirectory       = $modified_form_data[DirectoriesHandler::SUBDIRECTORY_KEY];
            $uploadedFiles      = $original_form_data[FilesHandler::FILE_KEY];

            foreach ($uploadedFiles as $uploadedFile) {
                $this->fileUploader->upload($uploadedFile, $uploadType, $subdirectory);
            }

        }

    }

}