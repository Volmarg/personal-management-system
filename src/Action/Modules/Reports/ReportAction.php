<?php

namespace App\Action\Modules\Reports;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Utils\Utils;
use App\Repository\AbstractRepository;
use Doctrine\DBAL\DBALException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportAction extends AbstractController {

    const TWIG_TEMPLATE_PAYMENT_SUMMARIES     = "modules/my-reports/monthly-payments-summaries.twig";
    const TWIG_TEMPLATE_PAYMENTS_CHARTS       = "modules/my-reports/payments-charts.twig";
    const TWIG_TEMPLATE_SAVINGS_CHARTS        = "modules/my-reports/savings-charts.twig";
    const TWIG_TEMPLATE_HISTORICAL_MONEY_OWED = "modules/my-reports/historical-money-owed.twig";

    const TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_FOR_TYPES  = "modules/my-reports/components/charts/total-payments-amount-for-types.twig";
    const TWIG_TEMPLATE_PAYMENTS_CHART_EACH_TYPE_EACH_MONTH    = "modules/my-reports/components/charts/payments-for-types-each-month.twig";
    const TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_EACH_MONTH = "modules/my-reports/components/charts/total-payments-each-month.twig";

    const TWIG_TEMPLATE_SAVINGS_CHART_AMOUNT_EACH_MONTH = "modules/my-reports/components/charts/savings-each-month.twig";

    const KEY_AMOUNT_FOR_TYPE = 'amountForType';
    const KEY_TYPE            = 'type';

    const KEY_DATE            = "date";
    const KEY_AMOUNT          = "amount";

    const KEY_YEAR_AND_MONTH      = "yearAndMonth";
    const KEY_MONEY               = "money";
    const KEY_MONEY_WITHOUT_BILLS = "moneyWithoutBills";


    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers = null;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
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
     * 
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
     * @param Request $request
     * @Route("/reports/savings_charts", name="reports-savings-charts", methods="GET")
     * @return JsonResponse|Response
     * @throws DBALException
     */
    public function savingsCharts(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $rendered_template = $this->renderTemplateSavingsCharts(false);
            return $rendered_template;
        }

        $rendered_template = $this->renderTemplateSavingsCharts(true);
        $template_content  = $rendered_template->getContent();

        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     * @Route("/reports/historical-money-owed", name="reports-historical-money-owed", methods="GET")
     */
    public function historicalMoneyOWed(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $rendered_template = $this->renderTemplateHistoricalMoneyOwed(false);
            return $rendered_template;
        }

        $rendered_template = $this->renderTemplateHistoricalMoneyOwed(true);
        $template_content  = $rendered_template->getContent();

        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @return Response
     * @throws DBALException
     */
    private function renderChartTotalPaymentsAmountForTypes(): Response {

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
     * @param bool $ajax_render
     * @return Response
     */
    private function renderTemplateHistoricalMoneyOwed(bool $ajax_render): Response {
        $historical_money_owed_by_me     = $this->app->repositories->reportsRepository->fetchHistoricalMoneyOwedBy(true);
        $historical_money_owed_by_others = $this->app->repositories->reportsRepository->fetchHistoricalMoneyOwedBy(false);

        $template_data = [
            'ajax_render'                     => $ajax_render,
            'historical_money_owed_by_me'     => $historical_money_owed_by_me,
            'historical_money_owed_by_others' => $historical_money_owed_by_others,
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_HISTORICAL_MONEY_OWED, $template_data);
        return $rendered_template;
    }

    /**
     * @return Response
     * @throws DBALException
     */
    private function renderChartPaymentsForTypesEachMonth(): Response {

        $payments_for_types_each_month = $this->app->repositories->reportsRepository->fetchPaymentsForTypesEachMonth();

        $chart_values        = [];
        $chart_x_axis_values = [];
        $chart_colors        = [];

        foreach( $payments_for_types_each_month as $payments_for_type_each_month){

            $date   = $payments_for_type_each_month[self::KEY_DATE];
            $type   = $payments_for_type_each_month[self::KEY_TYPE];
            $amount = $payments_for_type_each_month[self::KEY_AMOUNT];

            $chart_x_axis_values[] = $date;
            $chart_colors[]        = Utils::randomHexColor();

            if( !array_key_exists($type, $chart_values) ){
                $chart_values[$type] = [];
            }
            $chart_values[$type][] = $amount;
        }

        $chart_x_axis_values = array_unique($chart_x_axis_values);
        usort($chart_x_axis_values, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $template_data = [
            'chart_colors'        => $chart_colors,
            'chart_values'        => $chart_values,
            'chart_x_axis_values' => $chart_x_axis_values,
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_EACH_TYPE_EACH_MONTH, $template_data);
        return $rendered_template;
    }

    /**
     * @return Response
     * @throws DBALException
     * 
     */
    private function renderChartPaymentsTotalAmountForEachMonth(): Response {

        $payments_total_for_each_month = $this->app->repositories->reportsRepository->buildPaymentsSummariesForMonthsAndYears();

        $chart_values        = [];
        $chart_x_axis_values = [];
        $chart_colors        = [];

        $type_without_bills = $this->app->translator->translate("charts.paymentsTotalAmountForEachMonth.types.withoutBills");
        $type_with_bills    = $this->app->translator->translate("charts.paymentsTotalAmountForEachMonth.types.withBills");

        foreach( $payments_total_for_each_month as $payment_total_for_each_month){

            $date                = $payment_total_for_each_month[self::KEY_YEAR_AND_MONTH];
            $money               = $payment_total_for_each_month[self::KEY_MONEY];
            $money_without_bills = $payment_total_for_each_month[self::KEY_MONEY_WITHOUT_BILLS];

            $chart_x_axis_values[] = $date;
            $chart_colors[]        = Utils::randomHexColor();

            if( !array_key_exists($type_without_bills, $chart_values) ){
                $chart_values[$type_without_bills] = [];
                $chart_values[$type_without_bills] = [];
            }

            if( !array_key_exists($type_with_bills, $chart_values) ){
                $chart_values[$type_with_bills] = [];
                $chart_values[$type_with_bills] = [];
            }

            $chart_values[$type_without_bills][] = $money_without_bills;
            $chart_values[$type_with_bills][]    = $money;
        }

        usort($chart_x_axis_values, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $template_data = [
            'chart_colors'        => $chart_colors,
            'chart_values'        => $chart_values,
            'chart_x_axis_values' => $chart_x_axis_values,
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_EACH_MONTH, $template_data);
        return $rendered_template;
    }

    /**
     * @return Response
     * @throws DBALException
     * @throws Exception
     */
    private function renderChartSavingsEachMonth(): Response {

        $payments_total_for_each_month = $this->app->repositories->reportsRepository->buildPaymentsSummariesForMonthsAndYears();
        $all_incomes                   = $this->app->repositories->myPaymentsIncomeRepository->findBy([AbstractRepository::FIELD_DELETED => 0]);
        $custom_color                  = Utils::randomHexColor();
        $group_name                    = $this->app->translator->translate("reports.saviingsCharts.group");

        $chart_values        = [
            $group_name => [] // required by front js lib - shown on amount box hover
        ];
        $chart_x_axis_values = [];
        $chart_colors        = [$custom_color];

        foreach( $payments_total_for_each_month as $payment_total_for_each_month){

            $date   = $payment_total_for_each_month[self::KEY_YEAR_AND_MONTH];
            $saving = 0;

            foreach( $all_incomes as $income ){

                $income_date = $income->getDate()->format('Y-m');

                if( $income_date === $date ){

                    $money_spent  = round((float) $payment_total_for_each_month[self::KEY_MONEY] , 2);
                    $income       = $income->getAmount();

                    $saving       = $income - $money_spent;
                    $saving       = ( $saving < 0 ? 0 : $saving );

                }
            }

            $chart_x_axis_values[] = $date;
            $chart_values[$group_name][] = ceil($saving);
        }

        // second foreach is required as the one above eliminates the risk of having 3 inputs for same month
        $monthly_saving_summary = 0;
        $months_count           = count($chart_values[$group_name]);

        foreach( $chart_values[$group_name] as $saving ){
            $monthly_saving_summary += $saving;
        }

        $average_monthly_saving = ( empty($monthly_saving_summary) ? 0 : $monthly_saving_summary/$months_count );

        usort($chart_x_axis_values, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $template_data = [
            'chart_colors'           => $chart_colors,
            'chart_values'           => $chart_values,
            'chart_x_axis_values'    => $chart_x_axis_values,
            'average_monthly_saving' => round($average_monthly_saving, 2, PHP_ROUND_HALF_DOWN),
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_SAVINGS_CHART_AMOUNT_EACH_MONTH, $template_data);
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
     * 
     */
    private function renderTemplatePaymentsCharts(bool $ajax_render): Response {
        $rendered_chart_total_payments_amount_for_types = $this->renderChartTotalPaymentsAmountForTypes();
        $rendered_chart_payment_for_type_each_month     = $this->renderChartPaymentsForTypesEachMonth();
        $rendered_chart_payment_total_for_each_month    = $this->renderChartPaymentsTotalAmountForEachMonth();

        $template_data = [
            'ajax_render'                                 => $ajax_render,
            'chart_total_payments_amount_for_types'       => $rendered_chart_total_payments_amount_for_types->getContent(),
            'rendered_chart_payment_for_type_each_month'  => $rendered_chart_payment_for_type_each_month->getContent(),
            'rendered_chart_payment_total_for_each_month' => $rendered_chart_payment_total_for_each_month->getContent(),
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHARTS, $template_data);
        return $rendered_template;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws DBALException
     */
    private function renderTemplateSavingsCharts(bool $ajax_render): Response {
        $rendered_chart_savings_each_month = $this->renderChartSavingsEachMonth();

        $template_data = [
            'ajax_render'              => $ajax_render,
            'chart_savings_each_month' => $rendered_chart_savings_each_month->getContent(),
        ];

        $rendered_template = $this->render(self::TWIG_TEMPLATE_SAVINGS_CHARTS, $template_data);
        return $rendered_template;
    }

}