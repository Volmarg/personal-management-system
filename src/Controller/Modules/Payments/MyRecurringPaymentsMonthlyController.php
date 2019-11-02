<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class MyRecurringPaymentsMonthlyController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyPaymentsSettingsController $my_payments_settings_controller
     */
    private $my_payments_settings_controller;

    public function __construct(Application $app, MyPaymentsSettingsController $my_payments_settings_controller) {
        $this->my_payments_settings_controller = $my_payments_settings_controller;
        $this->app = $app;
    }

    /**
     * @Route("/my-recurring-payments-monthly-settings", name="my-recurring-payments-monthly-settings")
     * @param Request $request
     * @return Response
     */
    public function displaySettings(Request $request) {
        $this->add($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->my_payments_settings_controller->renderSettingsTemplate(false);
        }
        return $this->my_payments_settings_controller->renderSettingsTemplate(true);
    }

    /**
     * @param $request
     */
    protected function add($request) {

        $recurring_payments_form = $this->app->forms->recurringPayments();
        $recurring_payments_form->handleRequest($request);

        if ($recurring_payments_form->isSubmitted() && $recurring_payments_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recurring_payments_form->getData());
            $em->flush();
        }

    }

    /**
     * @Route("/my-recurring-payments-monthly/remove/", name="my-recurring-payments-monthly-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_RECURRING_PAYMENT_MONTHLY_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->my_payments_settings_controller->renderSettingsTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-recurring-payments-monthly/update/" ,name="my-recurring-payments-monthly-update")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myRecurringPaymentMonthlyRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

}
