<?php

namespace App\Action\Modules\Goals;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\Goals\GoalsListController;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Response\Base\BaseResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-goals", name: "module.my_goals.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_GOALS])]
class GoalsListAction extends AbstractController
{

    public function __construct(
        private readonly MyTodoController    $todoController,
        private readonly GoalsListController $goalsListController
    ) {

    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allTodo     = $this->goalsListController->getGoals();
        $entriesData = $this->todoController->buildFrontDataArray($allTodo);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}