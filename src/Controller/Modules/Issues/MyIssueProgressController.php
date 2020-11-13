<?php


namespace App\Controller\Modules\Issues;


use App\Controller\Core\Application;
use App\Entity\Modules\Issues\MyIssueProgress;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssueProgressController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * Will return entity for given id, otherwise null if nothing was found
     *
     * @param int $id
     * @return MyIssueProgress|null
     */
    public function findOneById(int $id): ?MyIssueProgress
    {
        return $this->app->repositories->myIssueProgressRepository->findOneById($id);
    }

}