<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Payments\MyPaymentsBills;
use App\Entity\Modules\Payments\MyPaymentsBillsItems;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsBillsController extends AbstractController
{

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
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
     * @param bool $ajax_render
     * @return Response
     */
    protected function renderTemplate($ajax_render = false) {

        $bills_form         = $this->app->forms->paymentsBillsForm();
        $bills_items_form   = $this->app->forms->paymentsBillsItemsForm();

        $bills              = $this->app->repositories->myPaymentsBillsRepository->findBy(['deleted' => 0]);
        $bills_items        = $this->app->repositories->myPaymentsBillsItemsRepository->findBy(['deleted' => 0]);

        $bills_amounts_summaries = $this->buildAmountSummaries($bills, $bills_items);

        $data = [
            'bills_form'                => $bills_form->createView(),
            'bills_items_form'          => $bills_items_form->createView(),
            'bills_amounts_summaries'   => $bills_amounts_summaries,
            'ajax_render'               => $ajax_render,
            'bills'                     => $bills,
            'bills_items'               => $bills_items,
        ];

        $template = 'modules/my-payments/bills.html.twig';

        return $this->render($template, $data );
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
     * @throws \Exception
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
     * @throws \Exception
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
     */
    public function updateBillItem(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPaymentsBillsItemsRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param MyPaymentsBills[] $bills
     * @param MyPaymentsBillsItems[] $bills_items
     * @return array
     */
    private function buildAmountSummaries(array $bills, array $bills_items):array{

        $summary = [];

        foreach($bills as $bill){

            foreach($bills_items as $bill_item){

                $bill_id            = $bill->getId();
                $bill_id_for_item   = $bill_item->getBill()->getId();

                if( $bill_id === $bill_id_for_item ){

                    $amount = $bill_item->getAmount();

                    if( array_key_exists($bill_id, $summary) ){
                        $summary[$bill_id] = ( $summary[$bill_id] + $amount );
                    }else{
                        $summary[$bill_id] = $amount;
                    }

                }

            }

        }

        return $summary;
    }

}
