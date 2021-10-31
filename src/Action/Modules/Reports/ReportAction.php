<?php

namespace App\Action\Modules\Reports;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Utils\Utils;
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
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws Exception
     * @Route("/reports/monthly-payments-summaries", name="reports-monthly-payments-summaries", methods="GET")
     */
    public function monthlyPaymentsSummaries(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            $renderedTemplate = $this->renderTemplateMonthlyPaymentsSummaries(false);
            return $renderedTemplate;
        }

        $renderedTemplate = $this->renderTemplateMonthlyPaymentsSummaries(true);
        $templateContent  = $renderedTemplate->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getMonthlyPaymentsSummariesPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }


    /**
     * @param Request $request
     * @Route("/reports/payments_charts", name="reports-payments-charts", methods="GET")
     * @return JsonResponse|Response
     * @throws DBALException
     * @throws Exception
     *
     */
    public function paymentsCharts(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $renderedTemplate = $this->renderTemplatePaymentsCharts(false);
            return $renderedTemplate;
        }

        $renderedTemplate = $this->renderTemplatePaymentsCharts(true);
        $templateContent  = $renderedTemplate->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPaymentsChartsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param Request $request
     * @Route("/reports/savings_charts", name="reports-savings-charts", methods="GET")
     * @return JsonResponse|Response
     * @throws DBALException
     * @throws Exception
     */
    public function savingsCharts(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $renderedTemplate = $this->renderTemplateSavingsCharts(false);
            return $renderedTemplate;
        }

        $renderedTemplate = $this->renderTemplateSavingsCharts(true);
        $templateContent  = $renderedTemplate->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getSavingsChartsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     * @Route("/reports/historical-money-owed", name="reports-historical-money-owed", methods="GET")
     * @throws Exception
     */
    public function historicalMoneyOWed(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            $renderedTemplate = $this->renderTemplateHistoricalMoneyOwed(false);
            return $renderedTemplate;
        }

        $renderedTemplate = $this->renderTemplateHistoricalMoneyOwed(true);
        $templateContent  = $renderedTemplate->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getHistoricalMoneyOwedPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @return Response
     * @throws DBALException
     */
    private function renderChartTotalPaymentsAmountForTypes(): Response {

        $totalPaymentsAmountForTypes = $this->controllers->getReportsControllers()->fetchTotalPaymentsAmountForTypes();
        $chartLabels = [];
        $chartValues = [];
        $chartColors = [];

        foreach($totalPaymentsAmountForTypes as $totalPaymentsAmountForType){
            $chartLabel = $totalPaymentsAmountForType[self::KEY_TYPE];
            $chartValue = $totalPaymentsAmountForType[self::KEY_AMOUNT_FOR_TYPE];

            $chartLabels[] = $chartLabel;
            $chartValues[] = $chartValue;
            $chartColors[] = Utils::randomHexColor();
        }

        $templateData = [
            'chart_colors' => $chartColors,
            'chart_labels' => $chartLabels,
            'chart_values' => $chartValues,
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_FOR_TYPES, $templateData);
        return $renderedTemplate;
    }


    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderTemplateHistoricalMoneyOwed(bool $ajaxRender): Response {
        $historicalMoneyOwedByMe     = $this->controllers->getReportsControllers()->fetchHistoricalMoneyOwedBy(true);
        $historicalMoneyOwedByOthers = $this->controllers->getReportsControllers()->fetchHistoricalMoneyOwedBy(false);

        $templateData = [
            'ajax_render'                     => $ajaxRender,
            'historical_money_owed_by_me'     => $historicalMoneyOwedByMe,
            'historical_money_owed_by_others' => $historicalMoneyOwedByOthers,
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_HISTORICAL_MONEY_OWED, $templateData);
        return $renderedTemplate;
    }

    /**
     * @return Response
     * @throws DBALException
     */
    private function renderChartPaymentsForTypesEachMonth(): Response {

        $paymentsForTypesEachMonth = $this->controllers->getReportsControllers()->fetchPaymentsForTypesEachMonth();

        $chartValues      = [];
        $ChartXAxisValues = [];
        $chartColors      = [];

        foreach( $paymentsForTypesEachMonth as $paymentsForTypeEachMonth){

            $date   = $paymentsForTypeEachMonth[self::KEY_DATE];
            $type   = $paymentsForTypeEachMonth[self::KEY_TYPE];
            $amount = $paymentsForTypeEachMonth[self::KEY_AMOUNT];

            $ChartXAxisValues[] = $date;
            $chartColors[]      = Utils::randomHexColor();

            if( !array_key_exists($type, $chartValues) ){
                $chartValues[$type] = [];
            }
            $chartValues[$type][] = $amount;
        }

        $ChartXAxisValues = array_unique($ChartXAxisValues);
        usort($ChartXAxisValues, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $templateData = [
            'chart_colors'        => $chartColors,
            'chart_values'        => $chartValues,
            'chart_x_axis_values' => $ChartXAxisValues,
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_EACH_TYPE_EACH_MONTH, $templateData);
        return $renderedTemplate;
    }

    /**
     * @return Response
     * @throws DBALException
     *
     */
    private function renderChartPaymentsTotalAmountForEachMonth(): Response {

        $paymentsTotalForEachMonth = $this->controllers->getReportsControllers()->buildPaymentsSummariesForMonthsAndYears();

        $chartValues      = [];
        $chartXAxisValues = [];
        $chartColors      = [];

        $typeWithoutBills = $this->app->translator->translate("charts.paymentsTotalAmountForEachMonth.types.withoutBills");
        $typeWithBills    = $this->app->translator->translate("charts.paymentsTotalAmountForEachMonth.types.withBills");

        foreach( $paymentsTotalForEachMonth as $paymentTotalForEachMonth){

            $date              = $paymentTotalForEachMonth[self::KEY_YEAR_AND_MONTH];
            $money             = $paymentTotalForEachMonth[self::KEY_MONEY];
            $MoneyWithoutBills = $paymentTotalForEachMonth[self::KEY_MONEY_WITHOUT_BILLS];

            $chartXAxisValues[] = $date;
            $chartColors[]      = Utils::randomHexColor();

            if( !array_key_exists($typeWithoutBills, $chartValues) ){
                $chartValues[$typeWithoutBills] = [];
                $chartValues[$typeWithoutBills] = [];
            }

            if( !array_key_exists($typeWithBills, $chartValues) ){
                $chartValues[$typeWithBills] = [];
                $chartValues[$typeWithBills] = [];
            }

            $chartValues[$typeWithoutBills][] = $MoneyWithoutBills;
            $chartValues[$typeWithBills][]    = $money;
        }

        usort($chartXAxisValues, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $templateData = [
            'chart_colors'        => $chartColors,
            'chart_values'        => $chartValues,
            'chart_x_axis_values' => $chartXAxisValues,
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHART_TOTAL_AMOUNT_EACH_MONTH, $templateData);
        return $renderedTemplate;
    }

    /**
     * @return Response
     * @throws DBALException
     * @throws Exception
     */
    private function renderChartSavingsEachMonth(): Response {

        $paymentsTotalForEachMonth = $this->controllers->getReportsControllers()->buildPaymentsSummariesForMonthsAndYears();
        $allIncomes                = $this->controllers->getMyPaymentsIncomeController()->getAllNotDeletedSummedByYearAndMonth();
        $customColor               = Utils::randomHexColor();
        $groupName                 = $this->app->translator->translate("charts.savings.label");

        $chartValues = [
            $groupName => [] // required by front js lib - shown on amount box hover
        ];
        $chartXAxisValues = [];
        $chartColors      = [$customColor];

        foreach($paymentsTotalForEachMonth as $paymentTotalForEachMonth){

            $date   = $paymentTotalForEachMonth[self::KEY_YEAR_AND_MONTH];
            $saving = 0;

            foreach($allIncomes as $yearAndMonth => $incomeAmount){

                if( $yearAndMonth === $date ){

                    $moneySpent = round((float) $paymentTotalForEachMonth[self::KEY_MONEY] , 2);
                    $saving     = $incomeAmount - $moneySpent;
                    $saving     = ( $saving < 0 ? 0 : $saving );

                }
            }

            $chartXAxisValues[] = $date;
            $chartValues[$groupName][] = ceil($saving);
        }

        // second foreach is required as the one above eliminates the risk of having 3 inputs for same month
        $monthlySavingSummary = 0;
        $monthsCount          = count($chartValues[$groupName]);

        foreach( $chartValues[$groupName] as $saving ){
            $monthlySavingSummary += $saving;
        }

        $averageMonthlySaving = ( empty($monthlySavingSummary) ? 0 : $monthlySavingSummary/$monthsCount );

        usort($chartXAxisValues, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $templateData = [
            'chart_colors'           => $chartColors,
            'chart_values'           => $chartValues,
            'chart_x_axis_values'    => $chartXAxisValues,
            'average_monthly_saving' => round($averageMonthlySaving, 2, PHP_ROUND_HALF_DOWN),
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_SAVINGS_CHART_AMOUNT_EACH_MONTH, $templateData);
        return $renderedTemplate;
    }

    /**
     * @param $ajaxRender
     * @return Response
     * @throws DBALException
     */
    private function renderTemplateMonthlyPaymentsSummaries(bool $ajaxRender): Response
    {
        $data = $this->controllers->getReportsControllers()->buildPaymentsSummariesForMonthsAndYears();
        $templateData = [
            'ajax_render' => $ajaxRender,
            'data'        => $data,
            'page_title'  => $this->getMonthlyPaymentsSummariesPageTitle(),
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_PAYMENT_SUMMARIES, $templateData);
        return $renderedTemplate;
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     * @throws DBALException
     */
    private function renderTemplatePaymentsCharts(bool $ajaxRender): Response
    {
        $renderedChartTotalPaymentsAmountForTypes = $this->renderChartTotalPaymentsAmountForTypes();
        $renderedChartPaymentForTypeEachMonth     = $this->renderChartPaymentsForTypesEachMonth();
        $renderedChartPaymentTotalForEachMonth    = $this->renderChartPaymentsTotalAmountForEachMonth();

        $templateData = [
            'ajax_render'                                 => $ajaxRender,
            'chart_total_payments_amount_for_types'       => $renderedChartTotalPaymentsAmountForTypes->getContent(),
            'rendered_chart_payment_for_type_each_month'  => $renderedChartPaymentForTypeEachMonth->getContent(),
            'rendered_chart_payment_total_for_each_month' => $renderedChartPaymentTotalForEachMonth->getContent(),
            'page_title'                                  => $this->getPaymentsChartsPageTitle(),
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_PAYMENTS_CHARTS, $templateData);
        return $renderedTemplate;
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     * @throws DBALException
     */
    private function renderTemplateSavingsCharts(bool $ajaxRender): Response {
        $renderedChartSavingsEachMonth = $this->renderChartSavingsEachMonth();

        $templateData = [
            'ajax_render'              => $ajaxRender,
            'chart_savings_each_month' => $renderedChartSavingsEachMonth->getContent(),
            'page_title'               => $this->getSavingsChartsPageTitle(),
        ];

        $renderedTemplate = $this->render(self::TWIG_TEMPLATE_SAVINGS_CHARTS, $templateData);
        return $renderedTemplate;
    }

    /**
     * Will return historical money owed page title
     *
     * @return string
     */
    private function getHistoricalMoneyOwedPageTitle(): string
    {
        return $this->app->translator->translate('reports.historicalMoneyOwed.title');
    }

    /**
     * Will return monthly payments summaries page title
     *
     * @return string
     */
    private function getMonthlyPaymentsSummariesPageTitle(): string
    {
        return $this->app->translator->translate('reports.monthlyPaymentsSummaries.title');
    }

    /**
     * Will return savings charts page title
     *
     * @return string
     */
    private function getSavingsChartsPageTitle(): string
    {
        return $this->app->translator->translate('reports.savingsCharts.title');
    }

    /**
     * Will return payments charts page title
     *
     * @return string
     */
    private function getPaymentsChartsPageTitle(): string
    {
        return $this->app->translator->translate('reports.paymentsCharts.title');
    }

}