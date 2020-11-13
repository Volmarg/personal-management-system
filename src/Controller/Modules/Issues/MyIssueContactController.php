<?php


namespace App\Controller\Modules\Issues;


use App\Controller\Core\Application;
use App\Entity\Modules\Issues\MyIssueContact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssueContactController extends AbstractController
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
     * @return MyIssueContact|null
     */
    public function findOneById(int $id): ?MyIssueContact
    {
        return $this->app->repositories->myIssueContactRepository->findOneById($id);
    }

}