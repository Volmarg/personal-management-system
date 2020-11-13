<?php


namespace App\Action\Modules\Goals;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Goals\MyGoalsPayments;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalsSettingsAction extends AbstractController {

    const MY_GOALS          = 'MyGoals';
    const MY_GOALS_SUBGOALS = 'MySubgoals';
    const MY_GOALS_PAYMENTS = 'MyGoalsPayments';

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
    public function removeGoalPayment(Request $request) {
        $id = trim($request->request->get('id'));

        $response = $this->app->repositories->deleteById(
            Repositories::MY_GOALS_PAYMENTS_REPOSITORY_NAME,
            $id
        );

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();
            $message           = $this->app->translator->translate('messages.ajax.success.recordHasBeenRemoved');

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $template_content);
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
    public function updateGoalPayment(Request $request) {
        $parameters = $request->request->all();
        $entity_id  = trim($parameters['id']);

        $entity     = $this->controllers->getGoalsPaymentsController()->findOneById($entity_id);
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
        $form                = $this->app->forms->goalPaymentForm();
        $add_record_response = $this->addRecord($form, $request);

        if( Response::HTTP_OK !== $add_record_response->getStatusCode() ){
            return AjaxResponse::buildJsonResponseForAjaxCall($add_record_response->getStatusCode(), $add_record_response->getContent());
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $goals_payments_form  = $this->app->forms->goalPaymentForm();
        $all_goals_payments   = $this->controllers->getGoalsPaymentsController()->getAllNotDeleted();

        $data = [
            'ajax_render'           => $ajax_render,
            'goals_payments_form'   => $goals_payments_form->createView(),
            'all_goals_payments'    => $all_goals_payments,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-goals/settings.html.twig', $data);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return Response
     */
    private function addRecord(FormInterface $form, Request $request) {
        $form->handleRequest($request);

        /**
         * @var MyGoalsPayments|null $form_data
         */
        $form_data = $form->getData();

        if (
                    !is_null($form_data)
                &&  !is_null($this->controllers->getGoalsPaymentsController()->getOneByName($form_data->getName()))
        ) {
            $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new Response($record_with_this_name_exist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new Response($form_submitted_message,200);
    }


}