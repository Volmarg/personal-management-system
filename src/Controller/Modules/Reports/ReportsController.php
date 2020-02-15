<?php
namespace App\Controller\Modules\Reports;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{

    const TWIG_TEMPLATE_PAYMENT_SUMMARIES = "modules/my-reports/monthly-payments-summaries.twig";
    const TWIG_TEMPLATE_PAYMENTS_CHARTS   = "modules/my-reports/payments-charts.twig";

    const TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_FOR_TYPES = "modules/my-reports/components/charts/total-payments-amount-for-types.twig";

    const KEY_AMOUNT_FOR_TYPE = 'amountForType';
    const KEY_TYPE            = 'type';

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
     * @param Request $request
     * @Route("/reports/payments_charts", name="reports-payments-charts", methods="GET")
     * @return JsonResponse|Response
     * @throws DBALException
     */
    public function paymentsCharts(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $rendered_template = $this->renderTemplatePaymentsCharts(false);
            return $rendered_template;
        }

        $rendered_template = $this->renderTemplatePaymentsCharts(true);
        $template_content  = $rendered_template->getContent();

        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @return Response
     * @throws DBALException
     */
    public function renderChartTotalPaymentsAmountForTypes(): Response {

        $total_payments_amount_for_types = $this->app->repositories->reportsRepository->fetchTotalPaymentsAmountForTypes();
        $chart_labels = [];
        $chart_values = [];

        foreach($total_payments_amount_for_types as $total_payments_amount_for_type){
            $chart_label = $total_payments_amount_for_type[self::KEY_TYPE];
            $chart_value = $total_payments_amount_for_type[self::KEY_AMOUNT_FOR_TYPE];

            $chart_labels[] = $chart_label;
            $chart_values[] = $chart_value;
        }

        $template_data = [
            'chart_labels' => $chart_labels,
            'chart_values' => $chart_values,
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_FOR_TYPES, $template_data);
        return $rendered_template;
    }

    /**
     * @param $ajax_render
     * @return Response
     * @throws DBALException
     */
    private function renderTemplateMonthlyPaymentsSummaries(bool $ajax_render): Response {
        $data = $this->app->repositories->reportsRepository->buildPaymentsSummariesForMonthsAndYears();

        $template_data = [
            'ajax_render' => $ajax_render,
            'data'        => $data
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENT_SUMMARIES, $template_data);
        return $rendered_template;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws DBALException
     */
    private function renderTemplatePaymentsCharts(bool $ajax_render): Response {
        $rendered_chart_total_payments_amount_for_types = $this->renderChartTotalPaymentsAmountForTypes();

        $template_data = [
            'ajax_render'                           => $ajax_render,
            'chart_total_payments_amount_for_types' => $rendered_chart_total_payments_amount_for_types->getContent(),
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHARTS, $template_data);
        return $rendered_template;
    }

}