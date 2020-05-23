<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsSettingsAction extends AbstractController {

    const TWIG_RECURRING_PAYMENT_TEMPLATE_FOR_SETTINGS = 'modules/my-payments/components/recurring-payments-settings.html.twig';

    const KEY_SETTING_NAME_TYPE                = "type";
    const KEY_SETTING_NAME_CURRENCY_MULTIPLIER = "currency_multiplier";

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-payments-settings", name="my-payments-settings")
     * @param Request $request
     * @return Response
     * 
     */
    public function display(Request $request) {
        $setting_type = $request->request->all();
        $setting_type = reset($setting_type)['name'];

        switch ($setting_type) {
            case self::KEY_SETTING_NAME_TYPE:

                $this->addPaymentType($request);
                break;

            case self::KEY_SETTING_NAME_CURRENCY_MULTIPLIER:

                $this->controllers->getMyPaymentsSettingsController()->insertOrUpdateRecord($request);
                break;

        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        $template_content  = $this->renderSettingsTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-payments-settings/remove/", name="my-payments-settings-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_SETTINGS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderSettingsTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-payments-settings/update", name="my-payments-settings-update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myPaymentsSettingsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function addPaymentType(Request $request) {
        $payments_types_form = $this->app->forms->paymentsTypesForm();
        $payments_types_form->handleRequest($request);

        /**
         * @var MyPaymentsSettings $form_data
         */
        $form_data = $payments_types_form->getData();

        if (!is_null($form_data) && $this->app->repositories->myPaymentsSettingsRepository->findBy(['value' => $form_data->getValue()])) {
            $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($record_with_this_name_exist, 409);
        }

        if ($payments_types_form->isSubmitted() && $payments_types_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($form_submitted_message, 200);

    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    public function renderSettingsTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $recurring_payments_template_view = $this->renderRecurringPaymentTemplate();
        $payments_types                   = $this->app->repositories->myPaymentsSettingsRepository->getAllPaymentsTypes();

        return $this->render('modules/my-payments/settings.html.twig', [
            'recurring_payments_template_view'  => $recurring_payments_template_view->getContent(),
            'currency_multiplier_form'          => $this->app->forms->currencyMultiplierForm()->createView(),
            'payments_types_form'               => $this->app->forms->paymentsTypesForm()->createView(),
            'payments_types'                    => $payments_types,
            'ajax_render'                       => $ajax_render,
            'skip_rewriting_twig_vars_to_js'    => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderRecurringPaymentTemplate($ajax_render = false) {
        $recurring_payments_form    = $this->app->forms->recurringPaymentsForm();

        $all_recurring__payments    = $this->app->repositories->myRecurringPaymentMonthlyRepository->findBy(['deleted' => '0'], ['date' => 'ASC']);
        $payments_types             = $this->app->repositories->myPaymentsSettingsRepository->findBy(['deleted' => '0', 'name' => 'type']);

        return $this->render(self::TWIG_RECURRING_PAYMENT_TEMPLATE_FOR_SETTINGS, [
            'form'               => $recurring_payments_form->createView(),
            'ajax_render'        => $ajax_render,
            'recurring_payments' => $all_recurring__payments,
            'payments_types'     => $payments_types
        ]);
    }


}