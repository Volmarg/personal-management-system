<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use App\DTO\Modules\Issues\IssueCardDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssuesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @param array $issues
     * @return IssueCardDTO[]
     */
    public function buildIssuesCardsDtosFromIssues(array $issues): array
    {

        return [];
    }

}