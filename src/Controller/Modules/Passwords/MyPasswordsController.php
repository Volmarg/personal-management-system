<?php

namespace App\Controller\Modules\Passwords;

use App\Controller\Core\Application;
use App\Entity\Modules\Passwords\MyPasswords;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyPasswordsController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Will return one entity for given id, or null otherwise
     *
     * @param int $id
     * @return MyPasswords|null
     */
    public function findPasswordEntityById(int $id): ?MyPasswords
    {
        return $this->app->repositories->myPasswordsRepository->findPasswordEntityById($id);
    }

    /**
     * @return MyPasswords[]
     */
    public function findAllNotDeleted(): array
    {
        return $this->app->repositories->myPasswordsRepository->findAllNotDeleted();
    }

    /**
     * @param int $id
     * @return string
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getPasswordForId(int $id): string
    {
        return $this->app->repositories->myPasswordsRepository->getPasswordForId($id);
    }
}
