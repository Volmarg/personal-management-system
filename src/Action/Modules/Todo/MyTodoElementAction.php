<?php

namespace App\Action\Modules\Todo;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyTodoElementAction extends AbstractController {

    const KEY_ID   = "id";
    const KEY_NAME = "name";

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
     * @Route("/admin/todo/element/update/", name="todo-element-update")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateTodo(Request $request): JsonResponse
    {
        if( !$request->request->has(self::KEY_ID) )
        {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ID;
            $this->app->logger->error("Request is missing `id` key");
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if( !$request->request->has(self::KEY_NAME) )
        {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_NAME;
            $this->app->logger->error("Request is missing `todo` key");
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $ajax_response = new AjaxResponse();
        $code          = Response::HTTP_OK;

        $parameters = $request->request->all();

        dump($parameters);

        try{
            $id     = $parameters[self::KEY_ID];
            $entity = $this->app->repositories->myTodoElementRepository->find($id);

            $this->app->repositories->update($parameters, $entity);

            $this->app->em->persist($entity);
            $this->app->em->flush();

        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.todoElementStatusCouldNotBeenChanged');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $ajax_response->setMessage('TODO: DONE');
        $ajax_response->setCode($code);

        return $ajax_response->buildJsonResponse();

    }


}