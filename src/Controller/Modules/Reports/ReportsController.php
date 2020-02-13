<?php
namespace App\Controller\Modules\Reports;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{

    const TWIG_TEMPLATE_PAYMENT_SUMMARIES = "modules/my-reports/monthly-payments-summaries.twig";

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @Route("/reports/monthly-payments-summaries", name="reports-monthly-payments-summaries", methods="GET")
     */
    public function monthlyPaymentsSummaries(Request $request){

        if (!$request->isXmlHttpRequest()) {
            $rendered_template = $this->renderTemplateMonthlyPaymentsSummaries(false);
            return $rendered_template;
        }

        $rendered_template = $this->renderTemplateMonthlyPaymentsSummaries(true);
        $template_content  = $rendered_template->getContent();

        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param $ajax_render
     * @return Response
     * @throws DBALException
     */
    private function renderTemplateMonthlyPaymentsSummaries($ajax_render): Response {
        $data = $this->app->repositories->reportsRepository->buildPaymentsSummariesForMonthsAndYears();

        $template_data = [
            'ajax_render' => $ajax_render,
            'data'        => $data
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENT_SUMMARIES, $template_data);
        return $rendered_template;
    }

}