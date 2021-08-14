<?php

namespace App\Action\Modules\Payments;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyPaymentsBillsAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsBillsAction extends AbstractController {

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-payments-bills", name="my-payments-bills")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPaymentsBillsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * Handles the fronted ajax call for adding bill
     *
     * @Route("/my-payments-bills/add-bill", name="my-payments-bills-add")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addBill(Request $request): JsonResponse
    {
        $form = $this->app->forms->paymentsBillsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();

            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, "", $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handles the fronted ajax call for adding bill item
     *
     * @Route("/my-payments-bills/add-bill-item", name="my-payments-bills-items-add")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function addBillItem(Request $request): JsonResponse
    {
        $form = $this->app->forms->paymentsBillsItemsForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();

            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, "", $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/my-payments-bills/remove-bill/", name="my-payments-bills-remove-bill")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeBill(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_BILLS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }


    /**
     * @Route("/my-payments-bills/remove-bill-item/", name="my-payments-bills-remove-bill-item")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeBillItem(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_BILLS_ITEMS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-payments-bills/update-bill/" ,name="my-payments-bills-update-bill")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     */
    public function updateBill(Request $request) {
        $parameters = $request->request->all();
        $entityId   = $request->request->getInt("id");
        $entity     = $this->controllers->getMyPaymentsBillsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-payments-bills/update-bill-item/" ,name="my-payments-bills-update-bill-item")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     */
    public function updateBillItem(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyPaymentsBillsItemsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $billsForm        = $this->app->forms->paymentsBillsForm();
        $billsItemsForm   = $this->app->forms->paymentsBillsItemsForm();

        $bills             = $this->controllers->getMyPaymentsBillsController()->getAllNotDeleted();
        $billsItems        = $this->controllers->getMyPaymentsBillsItemsController()->getAllNotDeleted();

        $billsAmountsSummaries = $this->controllers->getMyPaymentsBillsController()->buildAmountSummaries($bills, $billsItems);

        $data = [
            'bills_form'                     => $billsForm->createView(),
            'bills_items_form'               => $billsItemsForm->createView(),
            'bills_amounts_summaries'        => $billsAmountsSummaries,
            'ajax_render'                    => $ajaxRender,
            'bills'                          => $bills,
            'bills_items'                    => $billsItems,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getPaymentsBillsPageTitle(),
        ];

        $template = 'modules/my-payments/bills.html.twig';

        return $this->render($template, $data );
    }

    /**
     * Will return payments bills page title
     *
     * @return string
     */
    private function getPaymentsBillsPageTitle(): string
    {
        return $this->app->translator->translate('payments.bills.title');
    }

}