<?php


namespace App\Action\Files;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Utils;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use App\Services\Files\FileUploader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

/**
 * @ModuleAnnotation(
 *     relatedModules=App\Controller\Modules\ModulesController::UPLOAD_MENU_RELATED_MODULES
 * )
 */
class FileUploadAction extends AbstractController {

    const FINE_UPLOAD_PAGE_TWIG_TEMPLATE = 'core/upload/upload-page-fine-upload.html.twig';
    const FINE_UPLOAD_ALLOWED_COUNT_OF_UPLOADED_FILES_PER_CALL = 1;

    // this is a key name used internally by the FineUpload JS plugin
    const KEY_FILE_NAME = "qqfilename";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var FileUploader $fileUploader
     */
    private FileUploader $fileUploader;

    public function __construct(
        Controllers  $controllers,
        Application  $app,
        FileUploader $fileUploader
    ) {
        $this->app          = $app;
        $this->controllers  = $controllers;
        $this->fileUploader = $fileUploader;
    }

    /**
     * Handles the upload call from FineUploader `js package`, is called each time for every file in upload list
     *
     * @Route("/upload/upload-files-via-fine-upload-plugin", name="upload_files_via_fine_upload_plugin", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function uploadFilesViaFineUploadPlugin(Request $request): JsonResponse
    {
        if ( !$request->request->has(FileUploadController::KEY_UPLOAD_MODULE_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_UPLOAD_MODULE_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if ( !$request->request->has(FileTagger::KEY_TAGS) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if ( !$request->request->has(self::KEY_FILE_NAME) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_FILE_NAME;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        try{
            $uploadModuleDir                          = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
            $tags                                     = $request->request->get(FileTagger::KEY_TAGS);
            $subdirectoryTargetPathInModuleUploadDir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
            $fileNameWithExtension                    = $request->request->get(self::KEY_FILE_NAME);
            $fileName                                 = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

            $uploadedFiles        = $request->files->all();
            $countOfUploadedFiles = count($uploadedFiles);
            if(self::FINE_UPLOAD_ALLOWED_COUNT_OF_UPLOADED_FILES_PER_CALL != $countOfUploadedFiles){
                throw new Exception("Called fine upload method for files upload, expected 1 file to handle, got : {$countOfUploadedFiles} files");
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = reset($uploadedFiles);
            $response = $this->fileUploader->upload(
                $uploadedFile,
                $request,
                $uploadModuleDir,
                $subdirectoryTargetPathInModuleUploadDir,
                $fileName,
                $uploadedFile->getExtension(),
                $tags
            );

        }catch(Exception| TypeError $e){
            $this->app->logExceptionWasThrown($e);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/upload/fine-upload", name="upload_fine_upload", methods={"GET"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displayFineUploadPage(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderFineUploadTemplate(false);
        }

        $templateContent = $this->renderFineUploadTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getUploadPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * This function is also used for generating content for dialog (quick upload widget)
     *
     * @param bool $ajaxRender
     * @param string|null $twigTemplate
     * @return Response
     */
    public function renderFineUploadTemplate(bool $ajaxRender, ?string $twigTemplate = null): Response
    {
        $moduleAndDirectorySelectForm = $this->app->forms->getModuleAndDirectorySelectForm()->createView();
        $uploadMaxFilesize            = preg_replace("/[^0-9]/","", ini_get('upload_max_filesize'));
        $postMaxSize                  = preg_replace("/[^0-9]/","", ini_get('post_max_size'));
        $maxUploadSizeMb              = ( $postMaxSize < $uploadMaxFilesize ? $postMaxSize : $uploadMaxFilesize );
        $maxUploadSizeBytes           = $maxUploadSizeMb * 1024 * 1024;

        $data = [
            'ajax_render'                      => $ajaxRender,
            'module_and_directory_select_form' => $moduleAndDirectorySelectForm,
            'max_upload_size_mb'               => $maxUploadSizeMb,
            'max_upload_size_bytes'            => $maxUploadSizeBytes,
            'page_title'                       => $this->getUploadPageTitle(),
        ];

        $template = $twigTemplate ?? self::FINE_UPLOAD_PAGE_TWIG_TEMPLATE;

        return $this->render($template, $data);
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    private function handleFileUpload(Request $request) {
        $form = $this->app->forms->uploadForm();
        $form->handleRequest($request);

        $message  = $this->app->translator->translate('responses.upload.noFilesWereUploaded');
        $response = new Response($message);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData        = $form->getData();
            $uploadTableData = $request->request->get(FileUploadController::KEY_UPLOAD_TABLE);

            $subdirectory    = $formData[DirectoriesHandler::SUBDIRECTORY_KEY];
            $uploadModuleDir = $formData[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $uploadedFiles   = $formData[FilesHandler::FILE_KEY];
            $maxFileUploads  = (int)ini_get('max_file_uploads');

            foreach ($uploadedFiles as $index => $uploadedFile) {

                $fileExtensionKey = FileUploadController::KEY_EXTENSION . $index;
                $filenameKey      = FileUploadController::KEY_FILENAME . $index;
                $tagKey           = FileUploadController::KEY_TAG . $index;

                $filename   = $uploadTableData[$filenameKey];
                $extension  = $uploadTableData[$fileExtensionKey];
                $tags       = $uploadTableData[$tagKey];

                $uploadedFilesCount = count($uploadedFiles);

                if( $uploadedFilesCount > $maxFileUploads){
                    $message  = $this->app->translator->translate('responses.upload.tryingToUploadMoreFilesThanAllowedTo');
                    $response = new Response($message);
                    break;
                }

                $response = $this->fileUploader->upload($uploadedFile, $request, $uploadModuleDir, $subdirectory, $filename, $extension, $tags);
            }

            $flashType  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flashType, $message);
        }
    }

    /**
     * Will return upload page title
     *
     * @return string
     */
    private function getUploadPageTitle(): string
    {
        return $this->app->translator->translate('fineUpload.title');
    }


}