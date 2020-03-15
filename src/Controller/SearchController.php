<?php

namespace App\Controller;

use App\Controller\Utils\Application;
use App\Repository\FilesSearchRepository;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
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

        //todo: add lock join
        $files_search_results = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags_array, FilesSearchRepository::SEARCH_TYPE_FILES, true);
        $notes_search_results = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags_array, FilesSearchRepository::SEARCH_TYPE_NOTES, true);

        $search_results = array_merge(
            $files_search_results,
            $notes_search_results
        );

        return new JsonResponse([
            'searchResults' => $search_results
        ]);

    }

}
