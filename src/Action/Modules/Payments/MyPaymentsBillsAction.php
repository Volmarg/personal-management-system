<?php

namespace App\Action\Modules\Payments;

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

class MyPaymentsBillsAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-payments-bills", name="my-payments-bills")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addBill($request);
        $this->addBillItem($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-payments-bills/add-bill", name="my-payments-bills-add")
     * @param Request $request
     */
    public function addBill(Request $request) {
        $form = $this->app->forms->paymentsBillsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }
    }

    /**
     * @Route("/my-payments-bills/add-bill-item", name="my-payments-bills-items-add")
     * @param Request $request
     */
    public function addBillItem(Request $request) {
        $form = $this->app->forms->paymentsBillsItemsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }
    }

    /**
     * @Route("/my-payments-bills/remove-bill/", name="my-payments-bills-remove-bill")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeBill(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_BILLS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }


    /**
     * @Route("/my-payments-bills/remove-bill-item/", name="my-payments-bills-remove-bill-item")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeBillItem(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_BILLS_ITEMS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-payments-bills/update-bill/" ,name="my-payments-bills-update-bill")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function updateBill(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPaymentsBillsRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-payments-bills/update-bill-item/" ,name="my-payments-bills-update-bill-item")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function updateBillItem(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPaymentsBillsItemsRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $bills_form         = $this->app->forms->paymentsBillsForm();
        $bills_items_form   = $this->app->forms->paymentsBillsItemsForm();

        $bills              = $this->app->repositories->myPaymentsBillsRepository->findBy(['deleted' => 0]);
        $bills_items        = $this->app->repositories->myPaymentsBillsItemsRepository->findBy(['deleted' => 0]);

        $bills_amounts_summaries = $this->controllers->getMyPaymentsBillsController()->buildAmountSummaries($bills, $bills_items);

        $data = [
            'bills_form'                     => $bills_form->createView(),
            'bills_items_form'               => $bills_items_form->createView(),
            'bills_amounts_summaries'        => $bills_amounts_summaries,
            'ajax_render'                    => $ajax_render,
            'bills'                          => $bills,
            'bills_items'                    => $bills_items,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        $template = 'modules/my-payments/bills.html.twig';

        return $this->render($template, $data );
    }

}