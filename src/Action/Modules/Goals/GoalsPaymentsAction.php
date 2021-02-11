<?php


namespace App\Action\Modules\Goals;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalsPaymentsAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers)
    {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("admin/goals/payments/list", name="goals_payments_list")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false) {

        $allPayments = $this->controllers->getGoalsPaymentsController()->getAllNotDeleted();

        $data = [
            'all_payments'                   => $allPayments,
            'ajax_render'                    => $ajaxRender,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
        ];

        return $this->render('modules/my-goals/payments.html.twig', $data);
    }

}