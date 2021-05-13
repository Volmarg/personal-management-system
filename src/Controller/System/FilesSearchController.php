<?php


namespace App\Controller\System;


use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Entity\System\LockedResource;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilesSearchController extends AbstractController
{

    const SEARCH_TYPE_FILES = 'files';
    const SEARCH_TYPE_NOTES = 'notes';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(Application $app, LockedResourceController $lockedResourceController) {
        $this->lockedResourceController = $lockedResourceController;
        $this->app = $app;
    }

    /**
     * @param array $tags
     * @param string $searchType
     * @param bool $doLikePercent
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getSearchResultsDataForTag(array $tags, string $searchType, bool $doLikePercent = false): array
    {
        $results         = $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags, $searchType, $doLikePercent);
        $filteredResults = [];
        foreach($results as $result){

            $moduleName = $result['module'];
            if( !$this->lockedResourceController->isAllowedToSeeResource("", LockedResource::TYPE_ENTITY, $moduleName, false) ){
                continue;
            }
            $filteredResults[] = $result;
        }

        return $filteredResults;
    }

}