<?php

namespace App\Controller\Modules\Passwords;

use App\Controller\Core\Application;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPasswordsGroupsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Will return all not deleted entities
     * @return MyPasswordsGroups[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->app->repositories->myPasswordsGroupsRepository->findAllNotDeleted();
    }

    /**
     * Will return single entity for given id, otherwise null is returned
     *
     * @param int $id
     * @return MyPasswordsGroups|null
     */
    public function findOneById(int $id): ?MyPasswordsGroups
    {
        return $this->app->repositories->myPasswordsGroupsRepository->findOneById($id);
    }

    /**
     * Will return single entity for given id, otherwise null is returned
     *
     * @param string $name
     * @return MyPasswordsGroups|null
     */
    public function findOneByName(string $name): ?MyPasswordsGroups
    {
        return $this->app->repositories->myPasswordsGroupsRepository->findOneByName($name);
    }
}
