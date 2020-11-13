<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MyScheduleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyScheduleTypeController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MyScheduleType|null
     */
    public function findOneById(int $id): ?MyScheduleType
    {
        return $this->app->repositories->myScheduleTypeRepository->findOneById($id);
    }

    /**
     * @return MyScheduleType[]
     */
    public function getAllNonDeletedTypes(): array
    {
        return $this->app->repositories->myScheduleTypeRepository->getAllNonDeletedTypes();
    }

    /**
     * Returns one entity for given name or null otherwise
     *
     * @param string $name
     * @return MyScheduleType|null
     */
    public function findOneByName(string $name): ?MyScheduleType
    {
        return $this->app->repositories->myScheduleTypeRepository->findOneByName($name);
    }

}
