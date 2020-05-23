<?php


namespace App\Action\Modules\Goals;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(Application $app) {
        $this->app  = $app;
    }

    /**
     * @Route("/admin/goals/settings/update", name="goals_settings_update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateGoal(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myGoalsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/admin/subgoals/settings/update", name="subgoals_settings_update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateSubgoal(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myGoalsSubgoalsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/admin/goals/settings/remove", name="goals_settings_remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeGoal(Request $request) {
        $id = trim($request->request->get('id'));

        $response = $this->app->repositories->deleteById(
            Repositories::MY_GOALS_REPOSITORY_NAME,
            $id
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
     * @Route("/admin/subgoals/settings/remove", name="subgoals_settings_remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeSubgoal(Request $request) {
        $id = trim($request->request->get('id'));

        $response = $this->app->repositories->deleteById(
            Repositories::MY_SUBGOALS_REPOSITORY_NAME,
            $id
        );

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();
            $message           = $this->app->translator->translate('messages.ajax.success.recordHasBeenRemoved');

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        $message = $this->app->translator->translate('messages.ajax.failure.couldNotRemoveRecord');

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
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

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        $message = $this->app->translator->translate('messages.ajax.failure.couldNotRemoveRecord');

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/admin/goals/payments/settings/update", name="goals_payments_settings_update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateGoalPayment(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myGoalsPaymentsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/admin/goals/settings/{type?}", name="goals_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {

        $attributes     = $request->attributes->all();
        $type           = $attributes['type'];

        switch($type){
            case static::MY_GOALS:
                $form = $this->app->forms->goalForm();
                break;
            case static::MY_GOALS_SUBGOALS:
                $form = $this->app->forms->subgoalForm();
                break;
            case static::MY_GOALS_PAYMENTS:
                $form = $this->app->forms->goalPaymentForm();
                break;
            case null:
                $form = null;
                break;
            default:
                $message = $this->app->translator->translate('exceptions.MyGoalsSettingsController.thisGoalTypeIsNotAllowed');
                throw new Exception($message);
        }

        if( !is_null($form) ){
            $this->addRecord($form, $request);
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $goals_form             = $this->app->forms->goalForm();
        $subgoals_form          = $this->app->forms->subgoalForm();
        $goals_payments_form    = $this->app->forms->goalPaymentForm();

        $all_goals              = $this->app->repositories->myGoalsRepository->findBy(['deleted' => 0]);
        $all_subgoals           = $this->app->repositories->myGoalsSubgoalsRepository->findBy(['deleted' => 0]);
        $all_goals_payments     = $this->app->repositories->myGoalsPaymentsRepository->findBy(['deleted' => 0]);

        $data = [
            'ajax_render'           => $ajax_render,
            'goals_form'            => $goals_form->createView(),
            'subgoals_form'         => $subgoals_form->createView(),
            'goals_payments_form'   => $goals_payments_form->createView(),
            'all_goals'             => $all_goals,
            'all_subgoals'          => $all_subgoals,
            'all_goals_payments'    => $all_goals_payments,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-goals/settings.html.twig', $data);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return JsonResponse
     * 
     */
    private function addRecord(FormInterface $form, Request $request) {
        $form->handleRequest($request);
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myGoalsRepository->findBy(['name' => $form_data->getName()])) {
            $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($record_with_this_name_exist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($form_submitted_message,200);
    }


}