<?php

namespace App\Action\Modules\Goals;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GoalsListAction
 * @package App\Action\Modules\Goals
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_GOALS
 * )
 */
class GoalsListAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers
     *
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {

        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("admin/goals/list", name="goals_list")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getListPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $allTodo         = $this->controllers->getGoalsListController()->getGoals();
        $goalModule      = $this->controllers->getModuleController()->getOneByName(ModulesController::MODULE_NAME_GOALS);
        $todoElementForm = $this->app->forms->todoElementForm();
        $allModules      = $this->controllers->getModuleController()->getAllActive();

        $allRelatableEntitiesDataDtosForModules = $this->controllers->getMyTodoController()->getAllRelatableEntitiesDataDtosForModulesNames();

        $data = [
            'all_modules'                                  => [$goalModule],
            'all_todo'                                     => $allTodo,
            'ajax_render'                                  => $ajaxRender,
            'show_add_todo_widget'                         => true,
            'todo_element_form'                            => $todoElementForm,
            'data_template_url'                            => $this->generateUrl('goals_list'),
            'all_modules'                                  => $allModules,
            'all_relatable_entities_data_dtos_for_modules' => $allRelatableEntitiesDataDtosForModules,
            'skip_rewriting_twig_vars_to_js'               => $skipRewritingTwigVarsToJs,
            'page_title'                                   => $this->getListPageTitle(),
        ];

        return $this->render('modules/my-todo/list.html.twig', $data);
    }

    /**
     * Will return goals list page title
     *
     * @return string
     */
    private function getListPageTitle(): string
    {
        return $this->app->translator->translate('goals.list.title');
    }
}