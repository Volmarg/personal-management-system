<?php

namespace App\Controller\Modules\Job;

use App\Controller\Core\Application;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyJobHolidaysPoolController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @return mixed[]
     * @throws DBALException
     */
    public function getHolidaysSummaryGroupedByYears(): array
    {
        return $this->app->repositories->myJobHolidaysPoolRepository->getHolidaysSummaryGroupedByYears();
    }

    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getAvailableDaysTotally(): int
    {
        return $this->app->repositories->myJobHolidaysPoolRepository->getAvailableDaysTotally();
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getAllPoolsYears(): array
    {
        return $this->app->repositories->myJobHolidaysPoolRepository->getAllPoolsYears();
    }

    /**
     * @param int $id
     * @return MyJobHolidaysPool|null
     * @throws NonUniqueResultException
     * @throws NonUniqueResultException
     */
    public function findOneEntityById(int $id):? MyJobHolidaysPool
    {
        return $this->app->repositories->myJobHolidaysPoolRepository->findOneEntityById($id);
    }

    /**
     * Will return all not deleted entities
     *
     * @return MyJobHolidaysPool[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->myJobHolidaysPoolRepository->getAllNotDeleted();
    }
}
