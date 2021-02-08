<?php

namespace App\Controller\Modules\Job;

use App\Controller\Core\Application;
use App\Entity\Modules\Job\MyJobHolidays;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyJobHolidaysController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Returns all not deleted entities
     *
     * @return MyJobHolidays[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myJobHolidaysRepository->getAllNotDeleted();
    }

    /**
     * @param int $id
     * @param bool $forceFetch - if true then will clear the cached result and get the data from DB
     * @return MyJobHolidays|null
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function findOneEntityByIdOrNull(int $id, bool $forceFetch = false):? MyJobHolidays
    {
        return $this->app->repositories->myJobHolidaysRepository->findOneEntityByIdOrNull($id, $forceFetch);
    }

}
