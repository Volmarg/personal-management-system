<?php


namespace App\Action\Modules\Goals;


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
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_payments       = $this->app->repositories->myGoalsPaymentsRepository->findBy(['deleted' => 0]);

        $data = [
            'all_payments'                   => $all_payments,
            'ajax_render'                    => $ajax_render,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-goals/payments.html.twig', $data);
    }

}