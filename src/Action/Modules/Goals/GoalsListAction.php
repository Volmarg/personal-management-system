<?php

namespace App\Action\Modules\Goals;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
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

    public function __construct(Application $app) {

        $this->app = $app;
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

        $all_todo = $this->app->repositories->myTodoRepository->getEntitiesForModuleName(ModulesController::MODULE_NAME_GOALS); // todo: move to controller

        $data = [
            'all_todo'                       => $all_todo,
            'ajax_render'                    => $ajax_render,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-todo/list.html.twig', $data);
    }

}