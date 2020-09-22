<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
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
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * Will return all goals which are the binded todoEntities
     */
    public function getGoals(): array
    {
       $todo_goals = $this->controllers->getMyTodoController()->getTodoForModule(ModulesController::MODULE_NAME_GOALS);
       return $todo_goals;
    }

    /**
     * Will return all subgoals which are the binded todoElementEntities
     */
    public function getSubgoals(): array
    {
        $todo_elements_goals = $this->controllers->getMyTodoElementController()->getTodoElementsForModule(ModulesController::MODULE_NAME_GOALS);
        return $todo_elements_goals;
    }

}
