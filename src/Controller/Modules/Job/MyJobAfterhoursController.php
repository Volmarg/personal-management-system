<?php

namespace App\Controller\Modules\Job;

use App\Controller\Core\Application;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyJobAfterhoursController extends AbstractController {

    const GENERAL_USAGE = 'general usage';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app   = $app;
    }


    /**
     * @return array
     */
    public function getTimeToSpend(): array {
        $afterhours = [];

        $goals = $this->app->repositories->myJobAfterhoursRepository->getGoalsWithTime();

        foreach ($goals as $goal) {
            $time_remaining         = $goal[MyJobAfterhoursRepository::TIME_SUMMARY_FIELD];
            $goal_key               = (is_null($goal[MyJobAfterhoursRepository::GOAL_FIELD]) ? static::GENERAL_USAGE : $goal[MyJobAfterhoursRepository::GOAL_FIELD]);
            $afterhours[$goal_key]  = $time_remaining;
        }

        return $afterhours;
    }

}
