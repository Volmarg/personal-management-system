<?php


namespace App\Action\Modules\Goals;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GoalsSettingsAction
 * @package App\Action\Modules\Goals
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_GOALS
 * )
 */
class GoalsSettingsAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

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
     * @Route("/admin/goals/payments/settings/remove", name="goals_payments_settings_remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeGoalPayment(Request $request): Response
    {
        $id = trim($request->request->get('id'));

        $response = $this->app->repositories->deleteById(
            Repositories::MY_GOALS_PAYMENTS_REPOSITORY_NAME,
            $id
        );

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();
            $message          = $this->app->translator->translate('messages.ajax.success.recordHasBeenRemoved');

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        $message = $this->app->translator->translate('messages.ajax.failure.couldNotRemoveRecord');

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/admin/goals/payments/settings/update", name="goals_payments_settings_update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateGoalPayment(Request $request): Response
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getGoalsPaymentsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/admin/goals/settings/{type?}", name="goals_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {
        $form              = $this->app->forms->goalPaymentForm();
        $addRecordResponse = $this->addRecord($form, $request);
        $ajaxResponse      = new AjaxResponse();

        if( Response::HTTP_OK !== $addRecordResponse->getStatusCode() ){
            $ajaxResponse->setCode($addRecordResponse->getStatusCode());
            $ajaxResponse->setMessage($addRecordResponse->getContent());
            return $ajaxResponse->buildJsonResponse();
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent  = $this->renderTemplate(true)->getContent();

        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setTemplate($templateContent);
        $ajaxResponse->setPageTitle($this->getSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $goalsPaymentsForm = $this->app->forms->goalPaymentForm();
        $allGoalsPayments  = $this->controllers->getGoalsPaymentsController()->getAllNotDeleted();

        $data = [
            'ajax_render'           => $ajaxRender,
            'goals_payments_form'   => $goalsPaymentsForm->createView(),
            'all_goals_payments'    => $allGoalsPayments,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getSettingsPageTitle(),
        ];

        return $this->render('modules/my-goals/settings.html.twig', $data);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return Response
     */
    private function addRecord(FormInterface $form, Request $request): Response
    {
        $form->handleRequest($request);

        $formData = $form->getData();
        if (
                    !is_null($formData)
                &&  !is_null($this->controllers->getGoalsPaymentsController()->getOneByName($formData->getName()))
        ) {
            $recordWithThisNameExist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new Response($recordWithThisNameExist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();
        }

        $formSubmittedMessage = $this->app->translator->translate('forms.general.success');
        return new Response($formSubmittedMessage,200);
    }

    /**
     * Will return the settings page title
     * @return string
     */
    private function getSettingsPageTitle(): string
    {
        return $this->app->translator->translate('goals.settings.title');
    }

}