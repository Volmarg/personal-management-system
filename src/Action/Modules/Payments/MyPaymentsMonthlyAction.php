<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Form\Modules\Payments\MyPaymentsMonthlyType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsMonthlyAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->controllers = $controllers;
        $this->app         = $app;
    }

    /**
     * @Route("/my-payments-monthly", name="my-payments-monthly")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    protected function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $form                       = $this->getForm();
        $monthly_form_view          = $form->createView();

        $columns_names              = $this->getDoctrine()->getManager()->getClassMetadata(MyPaymentsMonthly::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_payments               = $this->controllers->getMyPaymentsMonthlyController()->getAllNotDeleted();
        $dates_groups               = $this->controllers->getMyPaymentsMonthlyController()->fetchAllDateGroups();
        $payments_by_type_and_date  = $this->controllers->getMyPaymentsMonthlyController()->getPaymentsByTypes();
        $payments_types             = $this->controllers->getMyPaymentsSettingsController()->getAllPaymentsTypes();


        return $this->render('modules/my-payments/monthly.html.twig', [
            'form'                           => $monthly_form_view,
            'all_payments'                   => $all_payments,
            'columns_names'                  => $columns_names,
            'dates_groups'                   => $dates_groups,
            'ajax_render'                    => $ajax_render,
            'payments_by_type_and_date'      => $payments_by_type_and_date,
            'payments_types'                 => $payments_types,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js
        ]);
    }

    /**
     * @param $request
     */
    protected function addFormDataToDB($request) {
        $payments_form = $this->getForm();
        $payments_form->handleRequest($request);

        if ($payments_form->isSubmitted() && $payments_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($payments_form->getData());
            $em->flush();
        }

    }

    /**
     * @Route("/my-payments-monthly/remove/", name="my-payments-monthly-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_MONTHLY_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-payments-monthly/update/" ,name="my-payments-monthly-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters     = $request->request->all();
        $entity_id      = trim($parameters['id']);

        $entity         = $this->controllers->getMyPaymentsMonthlyController()->findOneById($entity_id);
        $response       = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    private function getForm() {
        return $this->createForm(MyPaymentsMonthlyType::class);
    }

}