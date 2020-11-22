<?php

namespace App\Action\Modules\Todo;

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

class MyTodoElementAction extends AbstractController {

    const KEY_ID   = "id";
    const KEY_NAME = "name";
    const KEY_TODO = "myTodo";

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
     * @Route("/admin/todo/element/remove/",name="todo-element-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

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

        $ajax_response = new AjaxResponse();
        $message       = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');
        $code          = Response::HTTP_OK;

        $parameters = $request->request->all();

        try{
            $element_id = $parameters[self::KEY_ID];
            $todo_id    = $parameters[self::KEY_TODO][self::KEY_ID];

            $are_all_todo_done_before_update = $this->controllers->getMyTodoController()->areAllElementsDone($todo_id);

            $element_entity = $this->controllers->getMyTodoElementController()->findOneById($element_id);
            $todo_entity    = $this->controllers->getMyTodoController()->findOneById($todo_id);

            $update_response = $this->app->repositories->update($parameters, $element_entity);

            if( Response::HTTP_OK != $update_response->getStatusCode() ){
                return AjaxResponse::initializeFromResponse($update_response)->buildJsonResponse();
            }

            /**
             * When updating the elements, the overall `completed` status should also be updated for the `todo`,
             */
            $are_all_todo_done_after_done = $this->controllers->getMyTodoController()->areAllElementsDone($todo_id);

            if( $are_all_todo_done_before_update !== $are_all_todo_done_after_done ){
                if($are_all_todo_done_after_done){
                    $message = $this->app->translator->translate('responses.todo.allTodoElementsAreDoneTodoIsCompleted');
                    $todo_entity->setCompleted(true);
                }else{
                    $message = $this->app->translator->translate('responses.todo.todoElementStatusHasBeenChanged');
                    $todo_entity->setCompleted(false);
                }

                $this->controllers->getMyTodoController()->save($todo_entity);
            }
        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.todoElementStatusCouldNotBeenChanged');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $ajax_response->setMessage($message);
        $ajax_response->setCode($code);

        return $ajax_response->buildJsonResponse();
    }


}