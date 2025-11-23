<?php

namespace App\Action\Modules\Goals;

use App\Attribute\ModuleAttribute;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\Module\Todo\MyTodoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-goals", name: "module.my_goals.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_GOALS])]
class GoalsListAction extends AbstractController
{

    public function __construct(
        private readonly MyTodoService    $todoService,
        private readonly MyTodoRepository $todoRepository
    ) {

    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allTodo     = $this->todoRepository->getEntitiesForModuleName(ModulesService::MODULE_NAME_GOALS);
        $entriesData = $this->todoService->buildFrontDataArray($allTodo);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}