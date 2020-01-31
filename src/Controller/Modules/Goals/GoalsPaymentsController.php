<?php

namespace App\Controller\Modules\Goals;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GoalsPaymentsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("admin/goals/payments/list", name="goals_payments_list")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    protected function renderTemplate($ajax_render = false) {

        $all_payments       = $this->app->repositories->myGoalsPaymentsRepository->findBy(['deleted' => 0]);

        $data = [
            'all_payments'  => $all_payments,
            'ajax_render'   => $ajax_render,
        ];

        return $this->render('modules/my-goals/payments.html.twig', $data);
    }

}
