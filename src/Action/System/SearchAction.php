<?php


namespace App\Action\System;


use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Controller\System\LockedResourceController;
use App\Entity\System\LockedResource;
use App\Repository\FilesSearchRepository;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchAction extends AbstractController {
    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var LockedResourceController $locked_resource_controller
     */
    private $locked_resource_controller;

    public function __construct(Application $app, LockedResourceController $locked_resource_controller) {
        $this->app                        = $app;
        $this->locked_resource_controller = $locked_resource_controller;
    }

    /**
     * @Route("api/search/get-results-data", name="api_search_get_results_data")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSearchResultsDataForTag(Request $request){

        if( !$request->request->has(FileTagger::KEY_TAGS) ){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new \Exception($message);
        }

        $tags_string  = $request->request->get(FileTagger::KEY_TAGS);
        $tags_array   = explode(',', $tags_string);

        $files_search_results = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags_array, FilesSearchRepository::SEARCH_TYPE_FILES, true);
        $notes_search_results = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags_array, FilesSearchRepository::SEARCH_TYPE_NOTES, true);

        foreach( $files_search_results as $index => $file_search_result ){
            $full_file_path      = $file_search_result['fullFilePath'];
            $file_directory_path = FilesHandler::trimFirstAndLastSlash(pathinfo($full_file_path, PATHINFO_DIRNAME));

            if(
                (
                    $this->locked_resource_controller->isResourceLocked($file_directory_path, LockedResource::TYPE_DIRECTORY, ModulesController::MODULE_NAME_FILES)
                    ||  $this->locked_resource_controller->isResourceLocked($file_directory_path, LockedResource::TYPE_DIRECTORY, ModulesController::MODULE_NAME_IMAGES)
                )
                &&  $this->locked_resource_controller->isSystemLocked()
            ){
                unset($files_search_results[$index]);
            }

        }

        foreach( $notes_search_results as $index => $note_search_result ){
            $note_id     = $note_search_result['noteId'];
            $category_id = $note_search_result['categoryId'];

            if(
                (
                    $this->locked_resource_controller->isResourceLocked($note_id, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES)
                    ||  $this->locked_resource_controller->isResourceLocked($category_id, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY)
                )
                &&  $this->locked_resource_controller->isSystemLocked()
            ){
                unset($notes_search_results[$index]);
            }

        }

        $search_results = array_merge(
            $files_search_results,
            $notes_search_results
        );

        return new JsonResponse([
            'searchResults' => $search_results
        ]);

    }

}