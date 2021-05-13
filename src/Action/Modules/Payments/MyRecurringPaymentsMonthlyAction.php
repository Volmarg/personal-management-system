<?php


namespace App\Action\Modules\Payments;


use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Services\Validation\EntityValidatorService;
use App\VO\Validators\ValidationResultVO;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyRecurringPaymentsMonthlyAction
 * @package App\Action\Modules\Payments
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PAYMENTS
 * )
 */
class MyRecurringPaymentsMonthlyAction extends AbstractController {

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var MyPaymentsSettingsAction $myPaymentsSettingsAction
     */
    private MyPaymentsSettingsAction $myPaymentsSettingsAction;

    /**
     * @var EntityValidatorService $entityValidator
     */
    private EntityValidatorService $entityValidator;

    public function __construct(Application $app, Controllers $controllers, MyPaymentsSettingsAction $myPaymentsSettingsAction, EntityValidatorService $entityValidator) {
        $this->myPaymentsSettingsAction = $myPaymentsSettingsAction;
        $this->entityValidator          = $entityValidator;
        $this->controllers              = $controllers;
        $this->app                      = $app;
    }

    /**
     * @Route("/my-recurring-payments-monthly-settings", name="my-recurring-payments-monthly-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request): Response
    {
        $validationResult = $this->add($request);
        $ajaxResponse     = new AjaxResponse();

        if (!$request->isXmlHttpRequest()) {
            return $this->myPaymentsSettingsAction->renderSettingsTemplate();
        }

        try{
            $templateContent = $this->myPaymentsSettingsAction->renderSettingsTemplate(true)->getContent();

            if( !$validationResult->isValid() ){
                $ajaxResponseForValidation = AjaxResponse::buildAjaxResponseForValidationResult(
                    $validationResult,
                    $this->app->forms->recurringPaymentsForm(),
                    $this->app->translator,
                    $templateContent
                );

                return $ajaxResponseForValidation->buildJsonResponse();
            }
        }catch (Exception $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }

        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setSuccess(true);
        $ajaxResponse->setTemplate($templateContent);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-recurring-payments-monthly/remove/", name="my-recurring-payments-monthly-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_RECURRING_PAYMENT_MONTHLY_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->myPaymentsSettingsAction->renderSettingsTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-recurring-payments-monthly/update/" ,name="my-recurring-payments-monthly-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyRecurringPaymentsMonthlyController()->findOneById($entityId);
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

        $recurringPaymentsForm = $this->app->forms->recurringPaymentsForm();
        $recurringPaymentsForm->handleRequest($request);

        if ($recurringPaymentsForm->isSubmitted() && $recurringPaymentsForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $recurringPayment = $recurringPaymentsForm->getData();
            $validationResult = $this->entityValidator->handleValidation($recurringPayment, EntityValidatorService::ACTION_CREATE);

            if( !$validationResult->isValid() ){
                return $validationResult;
            }

            $em->persist($recurringPayment);
            $em->flush();
        }

        return ValidationResultVO::buildValidResult();
    }
}