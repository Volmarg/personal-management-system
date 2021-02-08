<?php

namespace App\Controller\Modules\Contacts;

use App\Controller\Core\Application;
use App\Entity\Modules\Contacts\MyContact;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyContactsController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * This function will search for single (not deleted) entity with given id
     * @param int $id
     * @return MyContact|null
     */
    public function findOneById(int $id):?MyContact {
        return $this->app->repositories->myContactRepository->findOneById($id);
    }

    /**
     * @return MyContact[]
     */
    public function findAllNotDeleted():array
    {
        return $this->app->repositories->myContactRepository->findAllNotDeleted();
    }

    /**
     * This function flushes the $entity
     * @param MyContact $myContact
     * @param bool $searchAndRebuildEntity - this flag is needed in case of persisting entity built from form data (even if the id is the same)
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function saveEntity(MyContact $myContact, bool $searchAndRebuildEntity = false)
    {
        $this->app->repositories->myContactRepository->saveEntity($myContact, $searchAndRebuildEntity);
    }
}
