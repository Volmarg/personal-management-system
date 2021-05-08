<?php

namespace App\Action\Modules\Todo;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Todo\MyTodoElement;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_TODO
 * )
 */
class MyTodoElementAction extends AbstractController {

    const KEY_ID   = "id";
    const KEY_NAME = "name";
    const KEY_TODO = "myTodo";

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {

        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/admin/todo/element/remove/",name="todo-element-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_TODO_ELEMENT_REPOSITORY,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * Handles updating the @see MyTodoElement,
     *  triggered when updating directly Element, or clicking on the checkbox to change the state
     *
     * Info: avoid adding required parameter other than provided, as there are more than one calls to this method,
     *  in one case the call is triggered from `saveAction` the other when clicking on the checkbox (other properties are being updated)
     *
     * Todo: test the modal logic, test all the places where this logic is called, also in the goals module
     *
     * @Route("/admin/todo/element/update/", name="todo-element-update")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateTodo(Request $request): JsonResponse
    {
        if( !$request->request->has(self::KEY_ID) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ID;
            $this->app->logger->error($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if( !$request->request->has(self::KEY_TODO) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_TODO;
            $this->app->logger->error($message);
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $ajaxResponse = new AjaxResponse();
        $message      = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');
        $code         = Response::HTTP_OK;

        $parameters = $request->request->all();

        try{
            $elementId = $parameters[self::KEY_ID];
            $todoId    = $parameters[self::KEY_TODO][self::KEY_ID];

            $areAllTodoDoneBeforeUpdate = $this->controllers->getMyTodoController()->areAllElementsDone($todoId);

            $elementEntity = $this->controllers->getMyTodoElementController()->findOneById($elementId);
            $todoEntity    = $this->controllers->getMyTodoController()->findOneById($todoId);

            $updateResponse = $this->app->repositories->update($parameters, $elementEntity);
            if( Response::HTTP_OK != $updateResponse->getStatusCode() ){
                return AjaxResponse::initializeFromResponse($updateResponse)->buildJsonResponse();
            }

            /**
             * When updating the elements, the overall `completed` status should also be updated for the `todo`,
             */
            $areAllTodoDoneAfterDone = $this->controllers->getMyTodoController()->areAllElementsDone($todoId);

            if( $areAllTodoDoneBeforeUpdate !== $areAllTodoDoneAfterDone ){
                if($areAllTodoDoneAfterDone){
                    $message = $this->app->translator->translate('responses.todo.allTodoElementsAreDoneTodoIsCompleted');
                    $todoEntity->setCompleted(true);
                }else{
                    $message = $this->app->translator->translate('responses.todo.todoElementStatusHasBeenChanged');
                    $todoEntity->setCompleted(false);
                }

                $this->controllers->getMyTodoController()->save($todoEntity);
            }
        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.todoElementStatusCouldNotBeenChanged');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setMessage($message);
        $ajaxResponse->setCode($code);

        return $ajaxResponse->buildJsonResponse();
    }


}