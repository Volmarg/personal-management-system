<?php


namespace App\Action\Files;


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

class FileUploadAction extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE      = 'core/upload/upload-page.html.twig';
    const FINE_UPLOAD_PAGE_TWIG_TEMPLATE = 'core/upload/upload-page-fine-upload.html.twig';
    const FINE_UPLOAD_ALLOWED_COUNT_OF_UPLOADED_FILES_PER_CALL = 1;

    // this is a key name used internally by the FineUpload JS plugin
    const KEY_FILE_NAME = "qqfilename";

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var FileUploader $file_uploader
     */
    private $file_uploader;

    public function __construct(
        Controllers  $controllers,
        Application  $app,
        FileUploader $fileUploader
    ) {
        $this->app           = $app;
        $this->controllers   = $controllers;
        $this->file_uploader = $fileUploader;
    }

    // todo: remove the old upload methods, also adjust the dialog for new logic
    /**
     * @Route("/upload/", name="upload")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displayUploadPage(Request $request) {
        $this->sendData($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/upload/send", name="upload_send")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function sendData(Request $request){
        $this->handleFileUpload($request);

        $referer_url     = $request->server->get('HTTP_REFERER');
        $upload_page_url = $this->generateUrl('upload');

        if( $referer_url === $upload_page_url || empty($referer_url) ) {
            return $this->renderTemplate(false);
        }

        return $this->redirect($referer_url);
    }

    /**
     * This function is also used for generating content for dialog (quick upload widget)
     * @param bool $ajax_render
     * @param string|null $twig_template
     * @return Response
     * @throws Exception
     */
    public function renderTemplate(bool $ajax_render, ?string $twig_template = null)
    {
        $upload_max_filesize        = preg_replace("/[^0-9]/","", ini_get('upload_max_filesize'));
        $post_max_size              = preg_replace("/[^0-9]/","", ini_get('post_max_size'));
        $max_allowed_files_count    = ini_get('max_file_uploads');

        $max_upload_size_mb  = ( $post_max_size < $upload_max_filesize ? $post_max_size : $upload_max_filesize );

        $form = $this->app->forms->uploadForm();

        $data = [
            'ajax_render'               => $ajax_render,
            'form'                      => $form->createView(),
            'max_upload_size_mb'        => $max_upload_size_mb,
            'max_allowed_files_count'   => $max_allowed_files_count
        ];

        $template = $twig_template ?? static::UPLOAD_PAGE_TWIG_TEMPLATE;

        return $this->render($template, $data);
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
            $upload_module_dir                              = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
            $tags                                           = $request->request->get(FileTagger::KEY_TAGS);
            $subdirectory_target_path_in_module_upload_dir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
            $file_name_with_extension                       = $request->request->get(self::KEY_FILE_NAME);
            $file_name                                      = pathinfo($file_name_with_extension, PATHINFO_FILENAME);

            $uploaded_files          = $request->files->all();
            $count_of_uploaded_files = count($uploaded_files);
            if(self::FINE_UPLOAD_ALLOWED_COUNT_OF_UPLOADED_FILES_PER_CALL != $count_of_uploaded_files){
                throw new Exception("Called fine upload method for files upload, expected 1 file to handle, got : {$count_of_uploaded_files} files");
            }

            /** @var UploadedFile $uploaded_file */
            $uploaded_file = reset($uploaded_files);
            $response = $this->file_uploader->upload(
                $uploaded_file,
                $request,
                $upload_module_dir,
                $subdirectory_target_path_in_module_upload_dir,
                $file_name,
                $uploaded_file->getExtension(),
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

        $template_content = $this->renderFineUploadTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * This function is also used for generating content for dialog (quick upload widget)
     *
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    private function renderFineUploadTemplate(bool $ajax_render): Response
    {
        $module_and_directory_select_form = $this->app->forms->getModuleAndDirectorySelectForm()->createView();
        $upload_max_filesize              = preg_replace("/[^0-9]/","", ini_get('upload_max_filesize'));
        $post_max_size                    = preg_replace("/[^0-9]/","", ini_get('post_max_size'));
        $max_upload_size_mb               = ( $post_max_size < $upload_max_filesize ? $post_max_size : $upload_max_filesize );
        $max_upload_size_bytes            = $max_upload_size_mb * 1024 * 1024;

        $data = [
            'ajax_render'                      => $ajax_render,
            'module_and_directory_select_form' => $module_and_directory_select_form,
            'max_upload_size_mb'               => $max_upload_size_mb,
            'max_upload_size_bytes'            => $max_upload_size_bytes,
        ];

        return $this->render(self::FINE_UPLOAD_PAGE_TWIG_TEMPLATE, $data);
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

            $form_data         = $form->getData();
            $upload_table_data = $request->request->get(FileUploadController::KEY_UPLOAD_TABLE);

            $subdirectory       = $form_data[DirectoriesHandler::SUBDIRECTORY_KEY];
            $upload_module_dir  = $form_data[FileUploadController::KEY_UPLOAD_MODULE_DIR];
            $uploaded_files     = $form_data[FilesHandler::FILE_KEY];
            $max_file_uploads   = (int)ini_get('max_file_uploads');

            foreach ($uploaded_files as $index => $uploaded_file) {

                $file_extension_key  = FileUploadController::KEY_EXTENSION . $index;
                $filename_key        = FileUploadController::KEY_FILENAME . $index;
                $tag_key             = FileUploadController::KEY_TAG . $index;

                $filename   = $upload_table_data[$filename_key];
                $extension  = $upload_table_data[$file_extension_key];
                $tags       = $upload_table_data[$tag_key];

                $uploaded_files_count = count($uploaded_files);

                if( $uploaded_files_count > $max_file_uploads){
                    $message  = $this->app->translator->translate('responses.upload.tryingToUploadMoreFilesThanAllowedTo');
                    $response = new Response($message);
                    break;
                }

                $response = $this->file_uploader->upload($uploaded_file, $request, $upload_module_dir, $subdirectory, $filename, $extension, $tags);
            }

            $flash_type  = Utils::getFlashTypeForRequest($response);
            $message    = $response->getContent();

            $this->addFlash($flash_type, $message);
        }
    }

}