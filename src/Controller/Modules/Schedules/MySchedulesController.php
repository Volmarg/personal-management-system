<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MySchedulesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $all_schedules_types = [];

    public function __construct(Application $app) {
        $this->app = $app;
        $this->all_schedules_types = $app->repositories->myScheduleTypeRepository->getAllNonDeletedTypes();
    }

    /**
     * @param string $schedules_type
     * @return void
     * @throws \Exception
     */
    public function validateSchedulesType(string $schedules_type):void {

        $is_valid = false;

        foreach( $this->all_schedules_types as $schedule_type ) {
            if( $schedules_type === $schedule_type->getName() ){
                $is_valid = true;
            }
        }

        if( !$is_valid ){
            throw new \Exception("Schedules type name: {$schedules_type} is incorrect ");
        }

    }

}
