<?php

namespace App\Action\Modules\Schedules;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Form\Modules\Schedules\MyScheduleType;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MySchedulesAction extends AbstractController {

    const TWIG_TEMPLATE = 'modules/my-schedules/my-schedules.twig';

    /**
     * @var Application
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
     * @Route("/my-schedules/{schedulesType}", name="my_schedules")
     * @param Request $request
     * @param string $schedulesType
     * @return Response
     * @throws Exception
     */
    public function display(Request $request, string $schedulesType):Response {
        $this->controllers->getMySchedulesController()->validateSchedulesType($schedulesType);

        $this->addFormDataToDB($request, $schedulesType);
        if ( !$request->isXmlHttpRequest() ) {
            return $this->renderTemplate($schedulesType);
        }

        $templateContent = $this->renderTemplate($schedulesType, true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
    }

    /**
     * @param string $schedulesType
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    public function renderTemplate(string $schedulesType, bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $form = $form = $this->app->forms->scheduleForm([MyScheduleType::KEY_PARAM_SCHEDULES_TYPES => $schedulesType]);

        $schedules      = $this->app->repositories->myScheduleRepository->getSchedulesByScheduleTypeName($schedulesType);
        $schedulesTypes = $this->app->repositories->myScheduleTypeRepository->findBy(['deleted' => 0]);

        // todo: start part for new calendar logic
        $calendarsDataDtoArray = $this->controllers->getMyScheduleCalendarController()->fetchAllNonDeletedCalendarsData();
        $scheduleCalendarForm  = $this->app->forms->scheduleCalendarForm();
        // todo: end part of new calendar logic

        $data = [
            'form'                           => $form->createView(),
            'ajax_render'                    => $ajaxRender,
            'schedules'                      => $schedules,
            'schedules_types'                => $schedulesTypes,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'calendars_data_dto_array'       => $calendarsDataDtoArray,
            'schedule_calendar_form'         => $scheduleCalendarForm->createView(),
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

    /**
     * @param Request $request
     * @param string $schedulesType
     */
    private function addFormDataToDB(Request $request, string $schedulesType): void
    {

        $form = $this->app->forms->scheduleForm([MyScheduleType::KEY_PARAM_SCHEDULES_TYPES => $schedulesType]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-schedule/update/",name="my-schedule-update")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entityId   = $parameters['id'];

        $entity     = $this->controllers->getMySchedulesController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-schedule/remove/",name="my-schedule-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeScheduleById(Request $request): Response
    {

        $id            = $request->request->get('id');
        $schedule      = $this->controllers->getMySchedulesController()->findOneById($id);
        $schedulesType = $schedule->getScheduleType()->getName();

        $response = $this->app->repositories->deleteById(Repositories::MY_SCHEDULE_REPOSITORY, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate($schedulesType, true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

}