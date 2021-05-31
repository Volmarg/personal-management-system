<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Services\Validation\EntityValidatorService;
use App\Entity\Modules\Job\MyJobHolidays;
use App\VO\Validators\ValidationResultVO;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyJobHolidaysAction extends AbstractController {

    const KEY_CHOICES = 'choices';

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var EntityValidatorService $entityValidator
     */
    private EntityValidatorService $entityValidator;

    public function __construct(Application $app, Controllers $controllers, EntityValidatorService $entityValidator) {
        $this->app              = $app;
        $this->controllers      = $controllers;
        $this->entityValidator = $entityValidator;
    }

    /**
     * @Route("/my-job/holidays", name="my-job-holidays")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $allPoolsYears    = $this->controllers->getMyJobHolidaysPoolController()->getAllPoolsYears();
        $ajaxResponse     = new AjaxResponse();
        $validationResult = $this->add($request, $allPoolsYears);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        try{
            $templateContent = $this->renderTemplate(true)->getContent();

            if( !$validationResult->isValid() ){
                 $form = $this->app->forms->jobHolidaysForm([
                    self::KEY_CHOICES => $allPoolsYears,
                 ]);

                $ajaxResponseForValidation = AjaxResponse::buildAjaxResponseForValidationResult(
                    $validationResult,
                    $form,
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
        $ajaxResponse->setPageTitle($this->getJobHolidaysPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-job/holidays/update/",name="my-job-holidays-update")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyJobHolidaysController()->findOneEntityByIdOrNull($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-job/holidays/remove/",name="my-job-holidays-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_HOLIDAYS_REPOSITORY_NAME,
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
     * @param Request $request
     * @param array $allPoolsYears
     * @return ValidationResultVO
     * @throws Exception
     */
    private function add(Request $request, array $allPoolsYears): ValidationResultVO
    {

        $form = $this->app->forms->jobHolidaysForm([
            static::KEY_CHOICES => $allPoolsYears
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $jobHoliday       = $form->getData();
            $validationResult = $this->entityValidator->handleValidation($jobHoliday, EntityValidatorService::ACTION_CREATE);

            if ( $validationResult->isValid() ){
                $em = $this->getDoctrine()->getManager();
                $em->persist($jobHoliday);
                $em->flush();
            }

            return $validationResult;
        }

        return ValidationResultVO::buildValidResult();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws DBALException
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false) {

        $allPoolsYears               = $this->controllers->getMyJobHolidaysPoolController()->getAllPoolsYears();
        $allHolidaysSpent            = $this->controllers->getMyJobHolidaysController()->getAllNotDeleted();
        $jobHolidaysSummary          = $this->controllers->getMyJobHolidaysPoolController()->getHolidaysSummaryGroupedByYears();
        $jobHolidaysAvailableTotally = $this->controllers->getMyJobHolidaysPoolController()->getAvailableDaysTotally();

        $jobHolidaysForm  = $this->app->forms->jobHolidaysForm([
            static::KEY_CHOICES => $allPoolsYears
        ]);

        $twigData = [
            'ajax_render'                       => $ajaxRender,
            'all_holidays_spent'                => $allHolidaysSpent,
            'job_holidays_form'                 => $jobHolidaysForm->createView(),
            'job_holidays_summary'              => $jobHolidaysSummary,
            'job_holidays_available_totally'    => $jobHolidaysAvailableTotally,
            'skip_rewriting_twig_vars_to_js'    => $skipRewritingTwigVarsToJs,
            'page_title'                        => $this->getJobHolidaysPageTitle(),
        ];

        return $this->render('modules/my-job/holidays.html.twig', $twigData);
    }

    /**
     * Will return job settings page title
     *
     * @return string
     */
    private function getJobHolidaysPageTitle(): string
    {
        return $this->app->translator->translate('job.holidays.title');
    }

}