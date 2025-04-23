<?php

namespace App\Action\Modules\Todo;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\Modules\Todo\MyTodoElement;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-todo-element", name: "module.my_todo.element.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_TODO])]
class MyTodoElementAction extends AbstractController {

    public function __construct(
        private readonly MyTodoController       $todoController,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param MyTodo  $todo
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "new", methods: [Request::METHOD_POST])]
    public function new(MyTodo $todo, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $todo);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @ParamConverter("todo", options={"mapping": {"todoId": "id"}})
     *
     * @param MyTodoElement $element
     * @param MyTodo        $todo
     * @param Request       $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}/{todoId}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyTodoElement $element, MyTodo $todo, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $todo, $element);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyTodoElement $element
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyTodoElement $element): JsonResponse
    {
        $element->setDeleted(true);
        $this->em->persist($element);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request            $request
     * @param MyTodo             $todo
     * @param MyTodoElement|null $element
     *
     * @throws DBALException
     * @throws Exception
     */
    private function createOrUpdate(Request $request, MyTodo $todo, ?MyTodoElement $element = null): void
    {
        if (!$element) {
            $element = new MyTodoElement();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $isCompleted = ArrayHandler::get($dataArray, 'isCompleted', true, false);
        $name        = ArrayHandler::get($dataArray, 'name', allowEmpty: false);

        $element->setName($name);
        $element->setMyTodo($todo);
        $element->setCompleted($isCompleted);

        $this->em->persist($element);
        $this->em->persist($todo);
        $this->em->flush();

        $areAllElementsDone = $this->todoController->areAllElementsDone($todo->getId());
        $todo->setCompleted($areAllElementsDone);

        $this->em->persist($todo);
        $this->em->flush();
    }

}