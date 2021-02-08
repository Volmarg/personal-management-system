<?php

namespace App\Controller\Modules\Job;

use App\Controller\Core\Application;
use App\Entity\Modules\Job\MyJobAfterhours;
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
            $goalKey               = (is_null($goal[MyJobAfterhoursRepository::GOAL_FIELD]) ? static::GENERAL_USAGE : $goal[MyJobAfterhoursRepository::GOAL_FIELD]);
            $afterhours[$goalKey]  = [
                MyJobAfterhoursRepository::TIME_SUMMARY_FIELD => $goal[MyJobAfterhoursRepository::TIME_SUMMARY_FIELD],
                MyJobAfterhoursRepository::DAYS_SUMMARY_FIELD => $goal[MyJobAfterhoursRepository::DAYS_SUMMARY_FIELD],
            ];
        }

        return $afterhours;
    }

    /**
     * Will search for not deleted afterhours by types
     *
     * @param string[] $types
     * @return MyJobAfterhours[]
     */
    public function findAllNotDeletedByType(array $types): array
    {
        $entities = $this->app->repositories->myJobAfterhoursRepository->findAllNotDeletedByType($types);
        return $entities;
    }

    /**
     * Will return one entity for given id, if such does not exist then null will be returned
     *
     * @param int $id
     * @return MyJobAfterhours|null
     */
    public function findOneById(int $id): ?MyJobAfterhours
    {
        return $this->app->repositories->myJobAfterhoursRepository->findOneById($id);
    }

    /**
     * @return array
     */
    public function getGoalsWithTime(): array
    {
        return $this->app->repositories->myJobAfterhoursRepository->getGoalsWithTime();
    }

}
