<?php

namespace App\Controller\Modules\Payments;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use App\Form\Modules\Payments\MyPaymentsProductsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\UtilsController;

class MyPaymentsProductsController extends AbstractController {

    const PRICE_COLUMN_NAME = 'price';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-payments-products", name="my-payments-products")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($this->getForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($this->getForm(), false);
        }
        return $this->renderTemplate($this->getForm(), true);
    }

    /**
     * @param FormInterface $form
     * @param bool $ajax_render
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderTemplate(FormInterface $form, $ajax_render = false) {
        $products_form_view = $form->createView();

        $column_names           = $this->getDoctrine()->getManager()->getClassMetadata(MyPaymentsProduct::class)->getColumnNames();
        $column_names           = $this->reorderPriceColumn($column_names);
        Repositories::removeHelperColumnsFromView($column_names);

        $products_all_data      = $this->app->repositories->myPaymentsProductRepository->findBy(['deleted' => 0]);
        $currency_multiplier    = $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier();

        return $this->render('modules/my-payments/products.html.twig',
            compact('column_names', 'products_all_data', 'products_form_view', 'currency_multiplier', 'ajax_render')
        );
    }

    protected function addFormDataToDB($products_form, $request) {
        $products_form->handleRequest($request);

        if ($products_form->isSubmitted($request) && $products_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($products_form->getData());
            $em->flush();
        }

    }

    /**
     * @Route("/my-payments-products/remove/", name="my-payments-products-remove")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function remove(Request $request) {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_PRODUCTS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($this->getForm(), true);
        }
        return $response;
    }

    /**
     * @Route("my-payments-products/update/",name="my-payments-products-update")
     */
    public function UpdateDataInDB(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myPaymentsProductRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    private function reorderPriceColumn($column_names) {
        $price_key = array_search(static::PRICE_COLUMN_NAME, $column_names);

        if (!array_key_exists(static::PRICE_COLUMN_NAME, $column_names)) {
            new \Exception("Key 'price' not found in products columns array");
        }

        unset($column_names[$price_key]);
        $column_names[] = static::PRICE_COLUMN_NAME;

        return $column_names;
    }

    private function getForm() {
        return $this->createForm(MyPaymentsProductsType::class);
    }
}
