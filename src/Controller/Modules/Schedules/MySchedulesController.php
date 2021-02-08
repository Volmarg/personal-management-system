<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MySchedule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MySchedulesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $allSchedulesTypes = [];

    public function __construct(Application $app) {
        $this->app = $app;
        $this->allSchedulesTypes = $app->repositories->myScheduleTypeRepository->getAllNonDeletedTypes();
    }

    /**
     * @param string $schedulesType
     * @return void
     * @throws \Exception
     */
    public function validateSchedulesType(string $schedulesType):void {

        $isValid = false;

        foreach($this->allSchedulesTypes as $scheduleType ) {
            if( $schedulesType === $scheduleType->getName() ){
                $isValid = true;
            }
        }

        if( !$isValid ){
            throw new \Exception("Schedules type name: {$schedulesType} is incorrect ");
        }

    }

    /**
     * Returns one entity for given id or null otherwise
     *
     * @param int $id
     * @return MySchedule|null
     */
    public function findOneById(int $id): ?MySchedule
    {
        return $this->app->repositories->myScheduleRepository->findOneById($id);
    }

    /**
     * Will return schedules entities incoming in days
     *
     * @param int $days
     * @return MySchedule[]
     */
    public function getIncomingSchedulesEntitiesInDays(int $days): array
    {
        return $this->app->repositories->myScheduleRepository->getIncomingSchedulesEntitiesInDays($days);
    }

}
