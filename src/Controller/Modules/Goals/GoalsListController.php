<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Controller\Modules\Todo\MyTodoElementController;
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
     * @var MyTodoController $my_todo_controller
     */
    private $my_todo_controller;

    public function __construct(Application $app, MyTodoController $my_todo_controller) {
        $this->app                = $app;
        $this->my_todo_controller = $my_todo_controller;
    }

    /**
     * Will return all goals which are the binded todoEntities
     */
    public function getGoals(): array
    {
       $todo_goals = $this->my_todo_controller->getTodoForModule(ModulesController::MODULE_NAME_GOALS);
       return $todo_goals;
    }

}
