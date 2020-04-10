<?php


namespace App\Action\Goals;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalsPaymentsAction extends AbstractController {

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
    private function renderTemplate($ajax_render = false) {

        $all_payments       = $this->app->repositories->myGoalsPaymentsRepository->findBy(['deleted' => 0]);

        $data = [
            'all_payments'  => $all_payments,
            'ajax_render'   => $ajax_render,
        ];

        return $this->render('modules/my-goals/payments.html.twig', $data);
    }

}