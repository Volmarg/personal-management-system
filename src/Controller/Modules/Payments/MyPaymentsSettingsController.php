<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Form\Modules\Payments\MyPaymentsSettingsCurrencyMultiplierType;
use App\Form\Modules\Payments\MyPaymentsTypesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsSettingsController extends AbstractController {

    const TWIG_RECURRING_PAYMENT_TEMPLATE_FOR_SETTINGS = 'modules/my-payments/components/recurring-payments-settings.html.twig';

    private $em;
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, EntityManagerInterface $em) {
        $this->em   = $em;
        $this->app  = $app;
    }

    /**
     * @Route("/my-payments-settings", name="my-payments-settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $setting_type = $request->request->all();
        $setting_type = reset($setting_type)['name'];

        switch ($setting_type) {
            case 'type':

                $payments_types_form = $this->getPaymentTypeForm();
                $response            = $this->addPaymentType($payments_types_form, $request);
                break;

            case 'currency_multiplier':

                $this->getCurrencyMultiplierForm()->handleRequest($request);
                $this->insertOrUpdateRecord($this->getCurrencyMultiplierForm(), $request);
                break;

        }

        if (isset($response) && $response instanceof Response && $response->getStatusCode() != 200) {
            return $response;
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        return $this->renderSettingsTemplate(true);

    }

    /**
     * @Route("/my-payments-settings/remove/", name="my-payments-settings-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_SETTINGS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderSettingsTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-payments-settings/update", name="my-payments-settings-update")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myPaymentsSettingsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param FormInterface $payments_types_form
     * @param Request $request
     * @return JsonResponse
     */
    protected function addPaymentType(FormInterface $payments_types_form, Request $request) {
        $payments_types_form->handleRequest($request);

        /**
         * @var MyPaymentsSettings $form_data
         */
        $form_data = $payments_types_form->getData();

        if (!is_null($form_data) && $this->app->repositories->myPaymentsSettingsRepository->findBy(['value' => $form_data->getValue()])) {
            return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
        }

        if ($payments_types_form->isSubmitted() && $payments_types_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED, 200);

    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    public function renderSettingsTemplate($ajax_render = false) {
        $recurring_payments_template_view = $this->renderRecurringPaymentTemplate();
        $payments_types                   = $this->app->repositories->myPaymentsSettingsRepository->getAllPaymentsTypes();

        return $this->render('modules/my-payments/settings.html.twig', [
            'recurring_payments_template_view'  => $recurring_payments_template_view->getContent(),
            'currency_multiplier_form'          => $this->getCurrencyMultiplierForm()->createView(),
            'payments_types_form'               => $this->getPaymentTypeForm()->createView(),
            'payments_types'                    => $payments_types,
            'ajax_render'                       => $ajax_render
        ]);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    public function renderRecurringPaymentTemplate($ajax_render = false) {
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

    private function getPaymentTypeForm() {
        return $this->createForm(MyPaymentsTypesType::class);
    }

    private function getCurrencyMultiplierForm() {
        return $this->createForm(MyPaymentsSettingsCurrencyMultiplierType::class);
    }

    /**
     * All this methods below were made the... wrong way. This should be changed at one point... somewhere... in future
     * @param $currency_multiplier_form
     * @param Request $request
     */

    protected function insertOrUpdateRecord($currency_multiplier_form, Request $request) {
        $currency_multiplier_form->handleRequest($request);

        if ($currency_multiplier_form->isSubmitted() && $currency_multiplier_form->isValid()) {
            $form_data          = $currency_multiplier_form->getData();
            $settings_epository = $this->em->getRepository(MyPaymentsSettings::class);

            if ($settings_epository->fetchCurrencyMultiplier()) {
                $this->updateCurrencyMultiplierRecord($settings_epository, $form_data);
                return;
            }
            $this->createRecord($form_data);
        }
    }

    private function updateCurrencyMultiplierRecord($repository, $form_data) {
        $orm_record = $repository->fetchCurrencyMultiplierRecord()[0];
        $orm_record->setValue($form_data->getValue());
        $this->em->persist($orm_record);
        $this->em->flush();
    }

    private function createRecord($record_data) {
        $this->em->persist($record_data);
        $this->em->flush();
    }
}
