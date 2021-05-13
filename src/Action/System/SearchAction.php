<?php


namespace App\Action\System;


use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use App\Controller\System\FilesSearchController;
use App\Entity\System\LockedResource;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchAction extends AbstractController {
    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers  $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("api/search/get-results-data", name="api_search_get_results_data")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception|Exception
     */
    public function getSearchResultsDataForTag(Request $request): Response
    {

        if( !$request->request->has(FileTagger::KEY_TAGS) ){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new \Exception($message);
        }

        $tagsString  = $request->request->get(FileTagger::KEY_TAGS);
        $tagsArray   = explode(',', $tagsString);

        $filesSearchResults = $this->controllers->getFilesSearchController()->getSearchResultsDataForTag($tagsArray, FilesSearchController::SEARCH_TYPE_FILES, true);
        $notesSearchResults = $this->controllers->getFilesSearchController()->getSearchResultsDataForTag($tagsArray, FilesSearchController::SEARCH_TYPE_NOTES, true);

        foreach( $filesSearchResults as $index => $fileSearchResult ){
            $fullFilePath      = $fileSearchResult['fullFilePath'];
            $fileDirectoryPath = FilesHandler::trimFirstAndLastSlash(pathinfo($fullFilePath, PATHINFO_DIRNAME));

            if(
                (
                        $this->controllers->getLockedResourceController()->isResourceLocked($fileDirectoryPath, LockedResource::TYPE_DIRECTORY, ModulesController::MODULE_NAME_FILES)
                    ||  $this->controllers->getLockedResourceController()->isResourceLocked($fileDirectoryPath, LockedResource::TYPE_DIRECTORY, ModulesController::MODULE_NAME_IMAGES)
                )
                &&  $this->controllers->getLockedResourceController()->isSystemLocked()
            ){
                unset($filesSearchResults[$index]);
            }

        }

        foreach( $notesSearchResults as $index => $noteSearchResult ){
            $noteId     = $noteSearchResult['noteId'];
            $categoryId = $noteSearchResult['categoryId'];

            if(
                (
                        $this->controllers->getLockedResourceController()->isResourceLocked($noteId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES)
                    ||  $this->controllers->getLockedResourceController()->isResourceLocked($categoryId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY)
                )
                &&  $this->controllers->getLockedResourceController()->isSystemLocked()
            ){
                unset($notesSearchResults[$index]);
            }

        }

        $searchResults = array_merge(
            $filesSearchResults,
            $notesSearchResults
        );

        return new JsonResponse([
            'searchResults' => $searchResults
        ]);

    }

}