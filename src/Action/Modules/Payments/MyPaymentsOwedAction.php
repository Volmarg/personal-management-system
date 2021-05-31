<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyPaymentsOwedAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyPaymentsOwedAction extends AbstractController {


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
     * @Route("/my-payments-owed", name="my-payments-owed")
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws Exception
     */
    public function display(Request $request) {
        $this->add($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getMoneyOwedPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-payments-owed/remove/", name="my-payments-owed-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_OWED_REPOSITORY_NAME,
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
     * @Route("my-payments-owed/update/" ,name="my-payments-owed-update")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     */
    public function update(Request $request) {
        $parameters    = $request->request->all();
        $entityId      = trim($parameters['id']);

        $entity         = $this->controllers->getMyPaymentsOwedController()->findOneById($entityId);
        $response       = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws DBALException
     * @throws Exception
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $form         = $this->app->forms->moneyOwedForm();
        $owedByMe     = $this->controllers->getMyPaymentsOwedController()->findAllNotDeletedFilteredByOwedStatus(true);
        $owedByOthers = $this->controllers->getMyPaymentsOwedController()->findAllNotDeletedFilteredByOwedStatus(false);

        $summaryOwedByOthers = $this->controllers->getMyPaymentsOwedController()->getMoneyOwedSummaryForTargetsAndOwningSide(false);
        $summaryOwedByMe     = $this->controllers->getMyPaymentsOwedController()->getMoneyOwedSummaryForTargetsAndOwningSide(true);

        $summaryOverall        = $this->controllers->getMyPaymentsOwedController()->fetchSummaryWhoOwesHowMuch();

        $summaryOverallOwedByMe     = [];
        $summaryOverallOwedByOthers = [];

        foreach( $summaryOverall as $summary ){
            $isSummaryOwedByMe = $summary['summaryOwedByMe'];

            if($isSummaryOwedByMe){
                $summaryOverallOwedByMe[] = $summary;
                continue;
            }

            $summaryOverallOwedByOthers[] = $summary;
        }

        $currenciesDtos = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();

        return $this->render('modules/my-payments/owed.html.twig', [
            'ajax_render'       => $ajaxRender,
            'form'              => $form->createView(),
            'owed_by_me'        => $owedByMe,
            'owed_by_others'    => $owedByOthers,
            'summary_owed_by_others' => $summaryOwedByOthers,
            'summary_owed_by_me'     => $summaryOwedByMe,
            'currencies_dtos'        => $currenciesDtos,
            'summary_overall_owed_by_me'     => $summaryOverallOwedByMe,
            'summary_overall_owed_by_others' => $summaryOverallOwedByOthers,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getMoneyOwedPageTitle(),
        ]);
    }

    /**
     * @param Request $request
     */
    private function add(Request $request) {
        $form = $this->app->forms->moneyOwedForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();
        }

    }

    /**
     * Will return payments owed page title
     *
     * @return string
     */
    public function getMoneyOwedPageTitle(): string
    {
        return $this->app->translator->translate('payments.moneyOwed.title');
    }

}