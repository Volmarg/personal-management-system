<?php


namespace App\Controller\Modules\Contacts;


use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContactGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyContactGroupController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @return MyContactGroup[]
     */
    public function getAllNotDeleted(): array {
        return $this->app->repositories->myContactGroupRepository->getAllNotDeleted();
    }

    /**
     * Will return one contact group for given name or null if nothing was found
     *
     * @param string $name
     * @return MyContactGroup|null
     */
    public function getOneByName(string $name):?MyContactGroup {
        return $this->app->repositories->myContactGroupRepository->getOneByName($name);
    }

    /**
     * Will return one entity for given id or null if nothing was found
     *
     * @param int $id
     * @return MyContactGroup|null
     */
    public function getOneById(int $id): ?MyContactGroup
    {
        return $this->app->repositories->myContactGroupRepository->getOneById($id);
    }

}