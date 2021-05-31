<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyPaymentsProductsAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsProductsAction extends AbstractController {

    const PRICE_COLUMN_NAME = 'price';

    /**
     * @var Application
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
     * @Route("/my-payments-products", name="my-payments-products")
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
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getProductsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-payments-products/remove/", name="my-payments-products-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_PRODUCTS_REPOSITORY_NAME,
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
     * @Route("my-payments-products/update/",name="my-payments-products-update")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     */
    public function updateDataInDB(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyPaymentsProductsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * Todo: check later why is this done this strange way....
     * @param $columnNames
     * @return array
     *
     * @throws Exception
     */
    private function reorderPriceColumn($columnNames): array
    {
        $priceKey = array_search(static::PRICE_COLUMN_NAME, $columnNames);

        if (!in_array(static::PRICE_COLUMN_NAME, $columnNames)) {
            $message = $this->app->translator->translate('exceptions.MyPaymentsProductsController.keyPriceNotFoundInProductsColumnsArray');
            throw new Exception($message);
        }

        unset($columnNames[$priceKey]);
        $columnNames[] = static::PRICE_COLUMN_NAME;

        return $columnNames;
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws Exception
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false) {
        $form             = $this->app->forms->paymentsProductsForm();
        $productsFormView = $form->createView();

        $columnNames = $this->getDoctrine()->getManager()->getClassMetadata(MyPaymentsProduct::class)->getColumnNames();
        $columnNames = $this->reorderPriceColumn($columnNames);
        Repositories::removeHelperColumnsFromView($columnNames);

        $productsAllData    = $this->controllers->getMyPaymentsProductsController()->getAllNotDeleted();
        $currencyMultiplier = $this->controllers->getMyPaymentsSettingsController()->fetchCurrencyMultiplier();

        $templateData = [
            'column_names'                   => $columnNames,
            'products_all_data'              => $productsAllData,
            'products_form_view'             => $productsFormView,
            'currency_multiplier'            => $currencyMultiplier,
            'ajax_render'                    => $ajaxRender,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getProductsPageTitle(),
        ];

        return $this->render('modules/my-payments/products.html.twig', $templateData);
    }

    /**
     * @param $request
     */
    private function addFormDataToDB($request) {
        $productsForm = $this->app->forms->paymentsProductsForm();
        $productsForm->handleRequest($request);

        if ($productsForm->isSubmitted() && $productsForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productsForm->getData());
            $em->flush();
        }

    }

    /**
     * Will return products page title
     *
     * @return string
     */
    public function getProductsPageTitle(): string
    {
        return $this->app->translator->translate('payments.title');
    }

}