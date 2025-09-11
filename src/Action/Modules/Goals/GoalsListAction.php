<?php

namespace App\Action\Modules\Goals;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Repository\Modules\Todo\MyTodoRepository;
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
        private readonly MyTodoRepository $todoRepository
    ) {

    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allTodo     = $this->todoRepository->getEntitiesForModuleName(ModulesController::MODULE_NAME_GOALS);
        $entriesData = $this->todoController->buildFrontDataArray($allTodo);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}