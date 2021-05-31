<?php

namespace App\Action\Modules\Todo;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Entity\Modules\Todo\MyTodo;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
class MyTodoListAction extends AbstractController {

    const KEY_TODO        = "myTodo";
    const KEY_MODULE_NAME = "moduleName";
    const KEY_ID          = "id";

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
     * @Route("admin/todo/list", name="todo_list")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->handleForms($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getTodoListPageTitle());

        return $ajaxResponse->buildJsonResponse();
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

        $ajaxResponse = new AjaxResponse();
        $message      = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');
        $code         = Response::HTTP_OK;

        $parameters = $request->request->all();

        $moduleName = $request->request->get(self::KEY_MODULE_NAME, null);
        $todoId     = $request->request->get(self::KEY_ID);

        try{
            /**
             * Some parameters are passed only to build final entity, must be unset before passing to repository update
             */
            if( array_key_exists(self::KEY_MODULE_NAME, $parameters) ){
                unset($parameters[self::KEY_MODULE_NAME]);
            }

            $moduleEntity = null;
            if( !empty($moduleName) ){
                $moduleEntity = $this->controllers->getModuleController()->getOneByName($moduleName);

                if( empty($moduleEntity) ){
                    $message = $this->app->translator->translate('responses.todo.moduleWithSuchNameDoesNotExist' . $moduleName);

                    $this->app->logger->critical($message);;
                    return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
                }
            }

            $todo = $this->controllers->getMyTodoController()->findOneById($todoId);
            $todo->setModule($moduleEntity);

            $updateResponse = $this->app->repositories->update($parameters, $todo);

            if( Response::HTTP_OK != $updateResponse->getStatusCode() ){
                return AjaxResponse::initializeFromResponse($updateResponse)->buildJsonResponse();
            }

        }catch(Exception $e){
            $message = $this->app->translator->translate('responses.todo.elementCouldNotBeUpdated'); //todo: add trans.
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setMessage($message);
        $ajaxResponse->setCode($code);

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $allGroupedTodo  = $this->controllers->getMyTodoController()->getAllGroupedByModuleName();
        $todoForm        = $this->app->forms->todoForm()->createView();
        $todoElementForm = $this->app->forms->todoElementForm();
        $allModules      = $this->controllers->getModuleController()->getAllActive();

        $allRelatableEntitiesDataDtosForModules = $this->controllers->getMyTodoController()->getAllRelatableEntitiesDataDtosForModulesNames();

        $data = [
            'all_grouped_todo'                             => $allGroupedTodo,
            'todo_form'                                    => $todoForm,
            'todo_element_form'                            => $todoElementForm, // direct object must be passed to render form multiple time
            'ajax_render'                                  => $ajaxRender,
            'all_modules'                                  => $allModules,
            'all_relatable_entities_data_dtos_for_modules' => $allRelatableEntitiesDataDtosForModules,
            'skip_rewriting_twig_vars_to_js'               => $skipRewritingTwigVarsToJs,
            'page_title'                                   => $this->getTodoListPageTitle(),
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
        $todoForm        = $this->app->forms->todoForm();
        $todoElementForm = $this->app->forms->todoElementForm();

        $todoForm->handleRequest($request);
        $todoElementForm->handleRequest($request);

        if($todoForm->isSubmitted() && $todoForm->isValid()){
            $todo = $todoForm->getData();
            $this->controllers->getMyTodoController()->save($todo);
        }

        if($todoElementForm->isSubmitted() && $todoElementForm->isValid()){
            $todoElement = $todoElementForm->getData();
            $this->controllers->getMyTodoElementController()->save($todoElement);
        }
    }

    /**
     * Will todo list page title
     *
     * @return string
     */
    private function getTodoListPageTitle(): string
    {
        return $this->app->translator->translate('todo.list.title');
    }

    /**
     * Will todo settings page title
     *
     * @return string
     */
    private function getTodoSettingsPageTitle(): string
    {
        return $this->app->translator->translate('todo.settings.title');
    }

}