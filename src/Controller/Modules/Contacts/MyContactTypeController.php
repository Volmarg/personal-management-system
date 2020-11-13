<?php


namespace App\Controller\Modules\Contacts;


use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContactType;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyContactTypeController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Returns image path for contactType by it's id
     * @param string $id
     * @return false|mixed
     * @throws DBALException
     */
    public function getImagePathForById(string $id)
    {
        return $this->app->repositories->myContactTypeRepository->getImagePathForById($id);
    }

    /**
     * Returns type name for contactType by it's id
     * @param string $id
     * @return false|mixed
     * @throws DBALException
     */
    public function getTypeNameById(string $id)
    {
        return $this->app->repositories->myContactTypeRepository->getTypeNameById($id);
    }

    /**
     * Will return one entity for given id, if none was found then null wil be returned
     *
     * @param int $id
     * @return MyContactType|null
     */
    public function findOneById(int $id): ?MyContactType
    {
        return $this->app->repositories->myContactTypeRepository->findOneById($id);
    }

    /**
     * Will return all not deleted contacts types
     *
     * @return MyContactType[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myContactTypeRepository->getAllNotDeleted();
    }

    /**
     * Will return one contact type if it was found for given name or null if nothing was found at all
     *
     * @param string $name
     * @return MyContactType|null
     */
    public function getOneByName(string $name): ?MyContactType
    {
        return $this->app->repositories->myContactTypeRepository->getOneByName($name);
    }

}