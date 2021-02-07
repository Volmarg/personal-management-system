<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Controller\Validators\Entities\EntityValidator;
use App\VO\Validators\ValidationResultVO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyRecurringPaymentsMonthlyAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var MyPaymentsSettingsAction $my_payments_settings_action
     */
    private $my_payments_settings_action;

    /**
     * @var EntityValidator $entity_validator
     */
    private EntityValidator $entity_validator;

    public function __construct(Application $app, Controllers $controllers, MyPaymentsSettingsAction $my_payments_settings_action, EntityValidator $entity_validator) {
        $this->my_payments_settings_action = $my_payments_settings_action;
        $this->entity_validator            = $entity_validator;
        $this->controllers                 = $controllers;
        $this->app                         = $app;
    }

    /**
     * @Route("/my-recurring-payments-monthly-settings", name="my-recurring-payments-monthly-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request): Response
    {
        $validation_result = $this->add($request);
        $ajax_response     = new AjaxResponse();

        if (!$request->isXmlHttpRequest()) {
            return $this->my_payments_settings_action->renderSettingsTemplate();
        }

        try{
            $template_content = $this->my_payments_settings_action->renderSettingsTemplate(true)->getContent();

            if( !$validation_result->isValid() ){
                $ajax_response_for_validation = AjaxResponse::buildAjaxResponseForValidationResult(
                    $validation_result,
                    $this->app->forms->recurringPaymentsForm(),
                    $this->app->translator,
                    $template_content
                );

                return $ajax_response_for_validation->buildJsonResponse();
            }
        }catch (Exception $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajax_response->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajax_response->setSuccess(false);
            $ajax_response->setMessage($message);

            return $ajax_response->buildJsonResponse();
        }

        $ajax_response->setCode(Response::HTTP_OK);
        $ajax_response->setSuccess(true);
        $ajax_response->setTemplate($template_content);

        return $ajax_response->buildJsonResponse();
    }

    /**
     * @Route("/my-recurring-payments-monthly/remove/", name="my-recurring-payments-monthly-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_RECURRING_PAYMENT_MONTHLY_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->my_payments_settings_action->renderSettingsTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-recurring-payments-monthly/update/" ,name="my-recurring-payments-monthly-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity_id  = trim($parameters['id']);

        $entity     = $this->controllers->getMyRecurringPaymentsMonthlyController()->findOneById($entity_id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param $request
     * @return ValidationResultVO
     * @throws Exception
     */
    private function add($request): ValidationResultVO
    {

        $recurring_payments_form = $this->app->forms->recurringPaymentsForm();
        $recurring_payments_form->handleRequest($request);

        if ($recurring_payments_form->isSubmitted() && $recurring_payments_form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $recurring_payment = $recurring_payments_form->getData();
            $validation_result = $this->entity_validator->handleValidation($recurring_payment, EntityValidator::ACTION_CREATE);

            if( !$validation_result->isValid() ){
                return $validation_result;
            }

            $em->persist($recurring_payment);
            $em->flush();
        }

        return ValidationResultVO::buildValidResult();
    }
}