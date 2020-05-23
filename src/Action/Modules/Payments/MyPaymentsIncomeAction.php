<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsIncomeAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("/my-payments-income", name="my-payments-income")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {
        $this->add($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-payments-income/remove/", name="my-payments-income-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_INCOME_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-payments-income/update/" ,name="my-payments-income-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPaymentsIncomeRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }


    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     * @throws Exception
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form            = $this->app->forms->moneyIncomeForm();
        $currencies_dtos = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();

        return $this->render('modules/my-payments/income.html.twig', [
            "records"                        => $this->app->repositories->myPaymentsIncomeRepository->findBy(['deleted' => 0]),
            'ajax_render'                    => $ajax_render,
            'form'                           => $form->createView(),
            'currencies_dtos'                => $currencies_dtos,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param Request $request
     */
    private function add(Request $request) {
        $form = $this->app->forms->moneyIncomeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

    }
}