<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Form\Modules\Payments\MyPaymentsMonthlyType;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyPaymentsMonthlyAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsMonthlyAction extends AbstractController {

    const KEY_CURRENT_ACTIVE_YEAR = "currentActiveYear";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->controllers = $controllers;
        $this->app         = $app;
    }

    /**
     * @Route("/my-payments-monthly/{year}", name="my-payments-monthly")
     * @param Request $request
     * @param string|null $year
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function display(Request $request, ?string $year = null): Response
    {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($year);
        }

        $templateContent = $this->renderTemplate($year, true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPaymentsMonthlyPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param string|null $year
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    protected function renderTemplate(?string $year = null, bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $form            = $this->getForm();
        $monthlyFormView = $form->createView();

        $years           = $this->controllers->getMyPaymentsSettingsController()->getYears();

        $allPayments           = [];
        $datesGroups           = [];
        $paymentsByTypeAndDate = [];
        $paymentsTypes         = [];

        $usedYear = $year;
        if( !empty($years) ){
            if( is_null($year) ){
                $latestYearIndex = array_key_first($years);
                $usedYear        = $years[$latestYearIndex];
            }

            $allPayments           = $this->controllers->getMyPaymentsMonthlyController()->getAllNotDeletedForYear($usedYear);
            $datesGroups           = $this->controllers->getMyPaymentsMonthlyController()->fetchAllDateGroupsForYear($usedYear);
            $paymentsByTypeAndDate = $this->controllers->getMyPaymentsMonthlyController()->getPaymentsByTypesForYear($usedYear);
            $paymentsTypes         = $this->controllers->getMyPaymentsSettingsController()->getAllPaymentsTypes();
        }

        return $this->render('modules/my-payments/monthly.html.twig', [
            'form'                           => $monthlyFormView,
            'all_payments'                   => $allPayments,
            'dates_groups'                   => $datesGroups,
            'ajax_render'                    => $ajaxRender,
            'payments_by_type_and_date'      => $paymentsByTypeAndDate,
            'payments_types'                 => $paymentsTypes,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'years'                          => $years,
            'active_year'                    => $usedYear,
            'page_title'                     => $this->getPaymentsMonthlyPageTitle(),
        ]);
    }

    /**
     * @param $request
     */
    protected function addFormDataToDB($request) {
        $paymentsForm = $this->getForm();
        $paymentsForm->handleRequest($request);

        if ($paymentsForm->isSubmitted() && $paymentsForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($paymentsForm->getData());
            $em->flush();
        }

    }

    /**
     * @Route("/my-payments-monthly/remove/", name="my-payments-monthly-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_MONTHLY_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message           = $response->getContent();
        $currentActiveYear = $request->request->get(self::KEY_CURRENT_ACTIVE_YEAR);

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate($currentActiveYear, true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-payments-monthly/update/" ,name="my-payments-monthly-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyPaymentsMonthlyController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @return FormInterface
     */
    private function getForm(): FormInterface
    {
        return $this->createForm(MyPaymentsMonthlyType::class);
    }

    /**
     * Will return payments monthly page title
     *
     * @return string
     */
    private function getPaymentsMonthlyPageTitle(): string
    {
        return $this->app->translator->translate('payments.monthlyPayments.title');
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