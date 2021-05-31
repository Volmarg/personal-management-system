<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyPaymentsSettingsAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsSettingsAction extends AbstractController {

    const TWIG_RECURRING_PAYMENT_TEMPLATE_FOR_SETTINGS = 'modules/my-payments/components/recurring-payments-settings.html.twig';

    const KEY_SETTING_NAME_TYPE                = "type";
    const KEY_SETTING_NAME_CURRENCY_MULTIPLIER = "currency_multiplier";

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var Application
     */
    private Application $app;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-payments-settings", name="my-payments-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $requestBag = $request->request->all();

        $settingType = null;
        if( !empty($requestBag) ){
            $settingType = reset($requestBag)['name'];
        }

        // todo: handle codes from responses
        switch ($settingType) {
            case self::KEY_SETTING_NAME_TYPE:

                $this->addPaymentType($request);
                break;

            case self::KEY_SETTING_NAME_CURRENCY_MULTIPLIER:

                $this->controllers->getMyPaymentsSettingsController()->insertOrUpdateRecord($request);
                break;

        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate();
        }

        $templateContent = $this->renderSettingsTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPaymentsSettingsTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-payments-settings/remove/", name="my-payments-settings-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_SETTINGS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderSettingsTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-payments-settings/update", name="my-payments-settings-update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $entityId   = $parameters['id'];

        $entity     = $this->controllers->getMyPaymentsSettingsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function addPaymentType(Request $request): JsonResponse
    {
        $paymentsTypesForm = $this->app->forms->paymentsTypesForm();
        $paymentsTypesForm->handleRequest($request);

        $formData = $paymentsTypesForm->getData();
        if (
                !is_null($formData)
            &&  !is_null($this->controllers->getMyPaymentsSettingsController()->findOneByValue($formData->getValue()))
        ) {
            $recordWithThisNameExist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($recordWithThisNameExist, 409);
        }

        if ($paymentsTypesForm->isSubmitted() && $paymentsTypesForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();
        }

        $formSubmittedMessage = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($formSubmittedMessage, 200);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    public function renderSettingsTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $recurringPaymentsTemplateView = $this->renderRecurringPaymentTemplate();
        $paymentsTypes                   = $this->controllers->getMyPaymentsSettingsController()->getAllPaymentsTypes();

        return $this->render('modules/my-payments/settings.html.twig', [
            'recurring_payments_template_view'  => $recurringPaymentsTemplateView->getContent(),
            'currency_multiplier_form'          => $this->app->forms->currencyMultiplierForm()->createView(),
            'payments_types_form'               => $this->app->forms->paymentsTypesForm()->createView(),
            'payments_types'                    => $paymentsTypes,
            'ajax_render'                       => $ajaxRender,
            'skip_rewriting_twig_vars_to_js'    => $skipRewritingTwigVarsToJs,
            'page_title'                        => $this->getPaymentsSettingsTitle(),
        ]);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderRecurringPaymentTemplate($ajaxRender = false): Response
    {
        $recurringPaymentsForm = $this->app->forms->recurringPaymentsForm();

        $allRecurringPayments = $this->controllers->getMyRecurringPaymentsMonthlyController()->getAllNotDeleted();
        $paymentsTypes        = $this->controllers->getMyPaymentsSettingsController()->getAllPaymentsTypes();

        return $this->render(self::TWIG_RECURRING_PAYMENT_TEMPLATE_FOR_SETTINGS, [
            'form'               => $recurringPaymentsForm->createView(),
            'ajax_render'        => $ajaxRender,
            'recurring_payments' => $allRecurringPayments,
            'payments_types'     => $paymentsTypes
        ]);
    }

    /**
     * Will return payments settings page title
     *
     * @return string
     */
    private function getPaymentsSettingsTitle(): string
    {
        return $this->app->translator->translate('payments.settings.title');
    }

}