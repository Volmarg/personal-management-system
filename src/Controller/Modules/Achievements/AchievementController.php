<?php

namespace App\Controller\Modules\Achievements;

use App\Controller\Core\Application;
use App\Entity\Modules\Achievements\Achievement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AchievementController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Will return all not deleted Achievements
     *
     * @return Achievement[]
     */
    public function getAllNotDeleted(): array
    {
        return $this->app->repositories->achievementRepository->getAllNotDeleted();
    }

    /**
     * Returns single entity found for given id or null if nothing was found
     *
     * @param int $id
     * @return Achievement|null
     */
    public function getOneById(int $id): ?Achievement
    {
        return $this->app->repositories->achievementRepository->getOneById($id);
    }

}
