<?php

namespace App\Action\Modules\Todo;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\Module;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-todo", name: "module.my_todo.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_TODO])]
class MyTodoListAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MyTodoController       $todoController,
        private readonly MyTodoRepository       $todoRepository
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "new", methods: [Request::METHOD_POST])]
    public function new(Request $request): JsonResponse
    {
        $this->createOrUpdate($request);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allTodo     = $this->todoRepository->getAll();
        $entriesData = $this->todoController->buildFrontDataArray($allTodo);
        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyTodo  $todo
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyTodo $todo, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $todo);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyTodo $todo
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyTodo $todo): JsonResponse
    {
        $todo->setDeleted(true);
        $this->em->persist($todo);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/relation-entries", name: "relation    _entries", methods: [Request::METHOD_POST])]
    public function getPossibleRelationEntries(Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $includedIds = ArrayHandler::checkAndGetKey($dataArray, 'includedIds', []);

        $entries = $this->todoController->getPossibleRelationEntries($includedIds);
        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entries);

        return $response->toJsonResponse();
    }

    /**
     * @param Request     $request
     * @param MyTodo|null $todo
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyTodo $todo = null): void
    {
        if (!$todo) {
            $todo = new MyTodo();
        }

        // cleanup old previous todo relation else duplication exception gets thrown
        if (!is_null($todo->getMyIssue())) {
            $todo->getMyIssue()->setTodo(null);
        }

        $dataArray       = RequestService::tryFromJsonBody($request);
        $moduleId        = ArrayHandler::get($dataArray, 'moduleId', true);
        $recordId        = ArrayHandler::get($dataArray, 'recordId', true);
        $name            = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $description     = ArrayHandler::get($dataArray, 'description');
        $showOnDashboard = ArrayHandler::get($dataArray, 'isForDashboard');

        $module = null;
        if ($moduleId) {
            $module = $this->em->find(Module::class, $moduleId);
            if (!$module) {
                throw new Exception("No module found for id: {$moduleId}");
            }
        }

        $todo->setName($name);
        $todo->setModule($module);
        $todo->setDescription($description);
        $todo->setDisplayOnDashboard($showOnDashboard);

        $this->em->persist($todo);

        // only issue records can relate to the todo so, not sending module name etc.
        if ($recordId) {
            $record = $this->em->find(MyIssue::class, $recordId);
            if ($record) {
                $record->setTodo($todo);
                $this->em->persist($record);
            }
        }

        $this->em->flush();
    }
}