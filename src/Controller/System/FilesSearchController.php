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
     * @param string $search_type
     * @param bool $do_like_percent
     * @return mixed[]
     * @throws DBALException
     */
    public function getSearchResultsDataForTag(array $tags, string $search_type, bool $do_like_percent = false)
    {
        return $this->app->repositories->filesSearchRepository->getSearchResultsDataForTag($tags, $search_type, $do_like_percent);
    }

}