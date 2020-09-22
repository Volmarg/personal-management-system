<?php

namespace App\Action\Modules\Todo;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// todo
//  - update name, description
//  - update task,
//  - add task,
//  - remove task,
//  - remove main point

// todo 2:
//  - change the demo data generator

class MyTodoListAction extends AbstractController {

    const KEY_TODO = "myTodo";
    const KEY_ID   = "id";

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
     * @Route("admin/todo/list", name="todo_list")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {

        $this->handleForms($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/admin/todo/update/",name="my-todo-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     * @throws Exception
     */
    public function updateTodo(Request $request): JsonResponse
    {
        if( !$request->request->has(self::KEY_ID) )
        {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ID;
            $this->app->logger->error("Request is missing `id` key");
            return AjaxResponse::buildJsonResponseForAjaxCall(400, $message);
        }

        if( !$request->request->has(self::KEY_TODO) )
        {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_TODO;
            $this->app->logger->error("Request is missing `todo` key");
            return AjaxResponse::buildJsonResponseForAjaxCall(400, $message);
        }

        $ajax_response = new AjaxResponse();
        $code          = Response::HTTP_OK;

        $parameters = $request->request->all();

        try{
            $element_id = $parameters[self::KEY_ID];
            $todo_id    = $parameters[self::KEY_TODO][self::KEY_ID];

            $entity = $this->app->repositories->myTodoElementRepository->find($element_id);

            $this->app->repositories->update($parameters, $entity);
            $todo = $this->app->repositories->myTodoRepository->find($todo_id);

            $this->app->em->persist($todo);
            $this->app->em->flush();

            $are_all_todo_done = $this->controllers->getMyTodoController()->areAllElementsDone($todo_id);

            if($are_all_todo_done){
                $message = $this->app->translator->translate('responses.todo.allTodoElementsAreDoneTodoIsCompleted');
                $todo->setCompleted(true);
            }else{
                $message = $this->app->translator->translate('responses.todo.todoElementStatusHasBeenChanged');
                $todo->setCompleted(false);
            }

        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.todoElementStatusCouldNotBeenChanged');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $ajax_response->setMessage($message);
        $ajax_response->setCode($code);

        return $ajax_response->buildJsonResponse();

    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_grouped_todo  = $this->controllers->getMyTodoController()->getAllGroupedByModuleName();
        $todo_form         = $this->app->forms->todoForm()->createView();
        $todo_element_form = $this->app->forms->todoElementForm();
        $all_modules       = $this->controllers->getModuleController()->getAllActive();

        $data = [
            'all_grouped_todo'               => $all_grouped_todo,
            'todo_form'                      => $todo_form,
            'todo_element_form'              => $todo_element_form, // direct object must be passed to render form multiple time
            'ajax_render'                    => $ajax_render,
            'all_modules'                    => $all_modules,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-todo/list.html.twig', $data);
    }

    /**
     * Will handle the forms for list view when these are submitted
     *
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function handleForms(Request $request)
    {
        $todo_form         = $this->app->forms->todoForm();
        $todo_element_form = $this->app->forms->todoElementForm();

        $todo_form->handleRequest($request);
        $todo_element_form->handleRequest($request);

        if($todo_form->isSubmitted() && $todo_form->isValid()){
            $todo = $todo_form->getData();
            $this->controllers->getMyTodoController()->save($todo);
        }

        if($todo_element_form->isSubmitted() && $todo_element_form->isValid()){
            $todo_element = $todo_element_form->getData();
            $this->controllers->getMyTodoElementController()->save($todo_element);
        }
    }

}