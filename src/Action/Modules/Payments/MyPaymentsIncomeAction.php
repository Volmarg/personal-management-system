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
 * Class MyPaymentsIncomeAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsIncomeAction extends AbstractController {

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers)
    {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-payments-income", name="my-payments-income")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->add($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPaymentsIncomePageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-payments-income/remove/", name="my-payments-income-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_INCOME_REPOSITORY_NAME,
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
     * @Route("my-payments-income/update/" ,name="my-payments-income-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters    = $request->request->all();
        $entityId      = trim($parameters['id']);

        $entity         = $this->controllers->getMyPaymentsIncomeController()->findOneById($entityId);
        $response       = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws Exception
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $form           = $this->app->forms->moneyIncomeForm();
        $currenciesDtos = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();

        return $this->render('modules/my-payments/income.html.twig', [
            "records"                        => $this->controllers->getMyPaymentsIncomeController()->getAllNotDeleted(),
            'ajax_render'                    => $ajaxRender,
            'form'                           => $form->createView(),
            'currencies_dtos'                => $currenciesDtos,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getPaymentsIncomePageTitle(),
        ]);
    }

    /**
     * @param Request $request
     */
    private function add(Request $request) {
        $form = $this->app->forms->moneyIncomeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();
        }

    }

    /**
     * Will return payments income page title
     *
     * @return string
     */
    private function getPaymentsIncomePageTitle(): string
    {
        return $this->app->translator->translate('payments.incomes.title');
    }
}