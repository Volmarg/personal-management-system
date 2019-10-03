<?php

namespace App\Controller;

use App\Controller\Utils\Application;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FilesSearchController extends AbstractController
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

        $search_results = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags_array, true);

        return new JsonResponse([
            'searchResults' => $search_results
        ]);

    }

}
