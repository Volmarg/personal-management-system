<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Form\Modules\Payments\MyPaymentsMonthlyType;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsMonthlyAction extends AbstractController {

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
     * @Route("/my-payments-monthly", name="my-payments-monthly")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    protected function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $form            = $this->getForm();
        $monthlyFormView = $form->createView();

        $columnsNames = $this->getDoctrine()->getManager()->getClassMetadata(MyPaymentsMonthly::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columnsNames);

        $allPayments           = $this->controllers->getMyPaymentsMonthlyController()->getAllNotDeleted();
        $datesGroups           = $this->controllers->getMyPaymentsMonthlyController()->fetchAllDateGroups();
        $paymentsByTypeAndDate = $this->controllers->getMyPaymentsMonthlyController()->getPaymentsByTypes();
        $paymentsTypes         = $this->controllers->getMyPaymentsSettingsController()->getAllPaymentsTypes();


        return $this->render('modules/my-payments/monthly.html.twig', [
            'form'                           => $monthlyFormView,
            'all_payments'                   => $allPayments,
            'columns_names'                  => $columnsNames,
            'dates_groups'                   => $datesGroups,
            'ajax_render'                    => $ajaxRender,
            'payments_by_type_and_date'      => $paymentsByTypeAndDate,
            'payments_types'                 => $paymentsTypes,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs
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

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
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

}