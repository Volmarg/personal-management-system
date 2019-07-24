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
     * @return \Symfony\Component\HttpFoundation\Response
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
            return $this->renderTemplate(false);
        }

        return $this->renderTemplate(true);

    }

    /**
     * @Route("/my-payments-settings/remove/", name="my-payments-settings-remove")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function remove(Request $request) {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_SETTINGS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-payments-settings/update", name="my-payments-settings-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderTemplate($ajax_render = false) {
        $payments_types = $this->app->repositories->myPaymentsSettingsRepository->getAllPaymentsTypes();

        return $this->render('modules/my-payments/settings.html.twig', [
            'currency_multiplier_form'  => $this->getCurrencyMultiplierForm()->createView(),
            'payments_types_form'       => $this->getPaymentTypeForm()->createView(),
            'payments_types'            => $payments_types,
            'ajax_render'               => $ajax_render
        ]);
    }

    private function getPaymentTypeForm() {
        return $this->createForm(MyPaymentsTypesType::class);
    }

    private function getCurrencyMultiplierForm() {
        return $this->createForm(MyPaymentsSettingsCurrencyMultiplierType::class, null, [
            'em' => $this->em
        ]);
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
