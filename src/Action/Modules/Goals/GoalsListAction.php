<?php

namespace App\Action\Modules\Goals;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Modules\ModulesController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalsListAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers
     *
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {

        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("admin/goals/list", name="goals_list")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_todo          = $this->controllers->getGoalsListController()->getGoals();
        $goal_module       = $this->controllers->getModuleController()->getOneByName(ModulesController::MODULE_NAME_GOALS);
        $todo_element_form = $this->app->forms->todoElementForm();

        $data = [
            'all_modules'                    => [$goal_module],
            'all_todo'                       => $all_todo,
            'ajax_render'                    => $ajax_render,
            'show_add_todo_widget'           => true,
            'todo_element_form'              => $todo_element_form,
            'data_template_url'              => $this->generateUrl('goals_list'),
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-todo/list.html.twig', $data);
    }

}