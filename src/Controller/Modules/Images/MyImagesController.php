<?php

namespace App\Controller\Modules\Images;

use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Dialogs;
use App\Controller\Utils\Env;
use App\Entity\FilesTags;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\FilesHandler;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyImagesController extends AbstractController {

    const TWIG_TEMPLATE_MY_IMAGES = 'modules/my-images/my-images.html.twig';
    const KEY_FILE_NAME           = 'file_name';
    const KEY_FILE_FULL_PATH      = 'file_full_path';
    const MODULE_NAME             = 'My Images';
    const TARGET_UPLOAD_DIR       = 'images';

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(FilesTagsController $files_tags_controller, Application $app) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');
        $this->files_tags_controller = $files_tags_controller;

        $this->app = $app;
    }

    /**
     * @Route("my-images/dir/{encoded_subdirectory_path?}", name="modules_my_images")
     * @param string|null $encoded_subdirectory_path
     * @param Request $request
     * @return Response
     */
    public function displayImages(? string $encoded_subdirectory_path, Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($encoded_subdirectory_path, false);
        }

        $template_content  = $this->renderCategoryTemplate($encoded_subdirectory_path, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param string|null $encoded_subdirectory_path
     * @param bool $ajax_render
     * @return array|RedirectResponse|Response
     */
    private function renderCategoryTemplate(? string $encoded_subdirectory_path, bool $ajax_render = false) {

        $module_upload_dir                      = Env::getImagesUploadDir();
        $decoded_subdirectory_path              = FilesHandler::trimFirstAndLastSlash(urldecode($encoded_subdirectory_path));
        $subdirectory_path_in_module_upload_dir = FileUploadController::getSubdirectoryPath($module_upload_dir, $decoded_subdirectory_path);

        if( !file_exists($subdirectory_path_in_module_upload_dir) ){
            $subdirectory_name = basename($decoded_subdirectory_path);
            $this->addFlash('danger', "Folder '{$subdirectory_name} does not exist.");
            return $this->redirectToRoute('upload');
        }

        if (empty($decoded_subdirectory_path)) {
            $all_images                 = $this->getMainFolderImages();
        } else {
            $decoded_subdirectory_path   = urldecode($decoded_subdirectory_path);
            $all_images                  = $this->getImagesFromCategory($decoded_subdirectory_path);
        }

        # count files in dir tree - disables button for folder removing on front
        $searchDir              = (empty($decoded_subdirectory_path) ? $module_upload_dir : $subdirectory_path_in_module_upload_dir);
        $files_count_in_tree    = FilesHandler::countFilesInTree($searchDir);

        $is_main_dir = ( empty($decoded_subdirectory_path) );

        $data = [
            'ajax_render'           => $ajax_render,
            'all_images'            => $all_images,
            'subdirectory_path'     => $decoded_subdirectory_path,
            'files_count_in_tree'   => $files_count_in_tree,
            'upload_module_dir'     => static::TARGET_UPLOAD_DIR,
            'is_main_dir'           => $is_main_dir
        ];

        return $this->render(static::TWIG_TEMPLATE_MY_IMAGES, $data);
    }

    /**
     * @param string $subdirectory
     * @return array
     */
    private function getImagesFromCategory(string $subdirectory) {
        $upload_dir       = Env::getImagesUploadDir();
        $all_images       = [];
        $search_dir       = ( empty($subdirectory) ? $upload_dir : $upload_dir . '/' . $subdirectory);

        $this->finder->files()->in($search_dir);

        foreach ($this->finder as $image) {

            $file_full_path = $image->getPath() . DIRECTORY_SEPARATOR . $image->getFilename();
            $file_tags      = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_full_path);
            $tags_json      = ( $file_tags instanceof FilesTags ? $file_tags->getTags() : "" );

            $all_images[] = [
                static::KEY_FILE_FULL_PATH => $image->getPathname(),
                static::KEY_FILE_NAME      => $image->getFilename(),
                FileTagger::KEY_TAGS       => $tags_json
            ];
        }

        return $all_images;
    }

    private function getMainFolderImages() {
        $all_images_paths = $this->getImagesFromCategory('');

        return $all_images_paths;
    }


    /**
     * Handles tags updating for the plugin modal
     * @Route("/api/my-images/update-tags", name="api_my_images_update_tags", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request){

        if (!$request->request->has(Dialogs::KEY_FILE_CURRENT_PATH)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . Dialogs::KEY_FILE_CURRENT_PATH;
            throw new \Exception($message);
        }

        if (!$request->request->has(FileTagger::KEY_TAGS)) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new \Exception($message);
        }

        $file_current_path = $request->request->get(Dialogs::KEY_FILE_CURRENT_PATH);
        $tags_string       = $request->request->get(FileTagger::KEY_TAGS);


        try{
            $this->files_tags_controller->updateTags($tags_string, $file_current_path);
            $message = $this->app->translator->translate('responses.tagger.tagsUpdated');
        } catch (\Exception $e){
            $message = $this->app->translator->translate('exceptions.tagger.thereWasAnError');
        }

        $response_data = [
            'response_code'     => 200,
            'response_message'  => $message
        ];

        return new JsonResponse($response_data);
    }


}
