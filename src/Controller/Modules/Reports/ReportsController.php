<?php
namespace App\Controller\Modules\Reports;

use App\Controller\Utils\Application;
use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{

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

        $ajax_render = true;

        if (!$request->isXmlHttpRequest()) {
            $ajax_render = false;
        }

        $data = $this->app->repositories->reportsRepository->buildPaymentsSummariesForMonthsAndYears();

        $template_data = [
            'ajax_render' => $ajax_render,
            'data'        => $data
        ];

        $template = 'modules/my-reports/monthly-payments-summaries.twig';

        return $this->render($template, $template_data);
    }

}