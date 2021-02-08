<?php


namespace App\Controller\System;


use App\Controller\Core\Application;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilesSearchController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param array $tags
     * @param string $searchType
     * @param bool $doLikePercent
     * @return mixed[]
     * @throws DBALException
     */
    public function getSearchResultsDataForTag(array $tags, string $searchType, bool $doLikePercent = false)
    {
        return $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags, $searchType, $doLikePercent);
    }

}