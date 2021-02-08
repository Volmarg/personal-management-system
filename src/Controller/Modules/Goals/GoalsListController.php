<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class GoalsListController
 * @package App\Controller\Modules\Goals
 */
class GoalsListController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyTodoController $myTodoController
     */
    private $myTodoController;

    public function __construct(Application $app, MyTodoController $myTodoController) {
        $this->app              = $app;
        $this->myTodoController = $myTodoController;
    }

    /**
     * Will return all goals which are the binded todoEntities
     */
    public function getGoals(): array
    {
       $todoGoals = $this->myTodoController->getTodoForModule(ModulesController::MODULE_NAME_GOALS);
       return $todoGoals;
    }

}
