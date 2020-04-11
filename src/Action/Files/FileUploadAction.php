<?php


namespace App\Action\Files;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Utils;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileUploader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadAction extends AbstractController {

    const UPLOAD_PAGE_TWIG_TEMPLATE     = 'core/upload/upload-page.html.twig';

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
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
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