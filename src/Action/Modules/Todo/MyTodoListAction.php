<?php

namespace App\Action\Modules\Todo;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Entity\Modules\Todo\MyTodo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyTodoListAction extends AbstractController {

    const KEY_TODO        = "myTodo";
    const KEY_MODULE_NAME = "moduleName";
    const KEY_ID          = "id";

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
     * Called when updating the @see MyTodo
     *
     * @Route("/admin/todo/update", name="my-todo-update")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateTodo(Request $request): JsonResponse
    {
        if( !$request->request->has(self::KEY_ID) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_TODO;
            $this->app->logger->error("Request is missing `todo` key");
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        $ajax_response = new AjaxResponse();
        $message       = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');
        $code          = Response::HTTP_OK;

        $parameters = $request->request->all();

        $module_name = $request->request->get(self::KEY_MODULE_NAME, null);
        $todo_id     = $request->request->get(self::KEY_ID);

        try{
            /**
             * Some parameters are passed only to build final entity, must be unset before passing to repository update
             */
            if( array_key_exists(self::KEY_MODULE_NAME, $parameters) ){
                unset($parameters[self::KEY_MODULE_NAME]);
            }

            $module_entity = null;
            if( !empty($module_name) ){
                $module_entity = $this->controllers->getModuleController()->getOneByName($module_name);

                if( empty($module_entity) ){
                    $message = $this->app->translator->translate('responses.todo.moduleWithSuchNameDoesNotExist' . $module_name);

                    $this->app->logger->critical($message);;
                    return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
                }
            }

            $todo = $this->controllers->getMyTodoController()->findOneById($todo_id);
            $todo->setModule($module_entity);

            $update_response = $this->app->repositories->update($parameters, $todo);

            if( Response::HTTP_OK != $update_response->getStatusCode() ){
                return AjaxResponse::initializeFromResponse($update_response)->buildJsonResponse();
            }

        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.elementCouldNotBeUpdated'); //todo: add trans.
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $ajax_response->setMessage($message);
        $ajax_response->setCode($code);

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
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

        $all_relatable_entities_data_dtos_for_modules = $this->controllers->getMyTodoController()->getAllRelatableEntitiesDataDtosForModulesNames();

        $data = [
            'all_grouped_todo'                             => $all_grouped_todo,
            'todo_form'                                    => $todo_form,
            'todo_element_form'                            => $todo_element_form, // direct object must be passed to render form multiple time
            'ajax_render'                                  => $ajax_render,
            'all_modules'                                  => $all_modules,
            'all_relatable_entities_data_dtos_for_modules' => $all_relatable_entities_data_dtos_for_modules,
            'skip_rewriting_twig_vars_to_js'               => $skip_rewriting_twig_vars_to_js,
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