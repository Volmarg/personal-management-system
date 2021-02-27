<?php

namespace App\Action\Modules\Schedules;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\DTO\Modules\Schedules\ScheduleDTO;
use App\Entity\Modules\Schedules\Schedule;
use App\Form\Modules\Schedules\MyScheduleType;
use App\Repository\Modules\Schedules\ScheduleRepository;
use DateTime;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

class MySchedulesAction extends AbstractController {

    const KEY_SCHEDULES_DTO_JSONS = "schedulesDtoJsons";
    const TWIG_TEMPLATE           = 'modules/my-schedules/my-schedules.twig';

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

    // TODO: new schedules logic

    const KEY_ID          = 'id';
    const KEY_TITLE       = 'title';
    const KEY_IS_ALL_DAY  = 'isAllDay';
    const KEY_START       = 'start';
    const KEY_END         = 'end';
    const KEY_CATEGORY    = 'category';
    const KEY_CALENDAR_ID = 'calendarId';
    const KEY_LOCATION    = 'location';

    /**
     * Will save single schedule (new one or updated one)
     *
     * @param Request $request
     * @param string|null $scheduleId
     * @return JsonResponse
     * @Route("/modules/schedules/save-schedule/{scheduleId}", methods={"POST"})
     */
    public function saveSchedule(Request $request, ?string $scheduleId = null): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();

        try{
            $json      = $request->getContent();
            $dataArray = json_decode($json, true);

            if( JSON_ERROR_NONE !== json_last_error() ){
                $this->app->logger->critical("Provided json from request is not valid", [
                    "json_last_error" => json_last_error_msg(),
                    "json"            => $json,
                ]);

                $message = $this->app->translator->translate('messages.general.couldNotHandleTheRequest');
                $ajaxResponse->setMessage($message);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                return $ajaxResponse->buildJsonResponse();
            }

            $title      = $dataArray[self::KEY_TITLE];
            $isAllDay   = $dataArray[self::KEY_IS_ALL_DAY];
            $start      = $dataArray[self::KEY_START];
            $end        = $dataArray[self::KEY_END];
            $category   = $dataArray[self::KEY_CATEGORY];
            $calendarId = $dataArray[self::KEY_CALENDAR_ID];
            $location   = $dataArray[self::KEY_LOCATION];

            $calendar = $this->controllers->getMyScheduleCalendarController()->findCalendarById($calendarId);
            if( is_null($calendar) ){
                $message = $this->app->translator->translate('schedules.calendar.messages.noCalendarHasBeenFoundForId', [
                    "%id%" => $calendarId
                ]);
                $ajaxResponse->setMessage($message);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                return $ajaxResponse->buildJsonResponse();
            }

            $schedule = new Schedule();
            if( !empty($scheduleId) ){

                $schedule = $this->controllers->getMySchedulesController()->findOneScheduleById($scheduleId);
                if( empty($schedule) ){

                    $message = $this->app->translator->translate('schedules.schedule.message.noScheduleHasBeenFoundForId', [
                        "%id%" => $scheduleId
                    ]);
                    $ajaxResponse->setMessage($message);
                    $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                    return $ajaxResponse->buildJsonResponse();
                }
            }

            $schedule->setTitle($title);
            $schedule->setAllDay($isAllDay);
            $schedule->setStart(new DateTime($start));
            $schedule->setEnd(new DateTime($end));
            $schedule->setCategory($category);
            $schedule->setLocation($location);
            $schedule->setCalendar($calendar);

            $this->controllers->getMySchedulesController()->saveSchedule($schedule);
        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * Will return all not deleted schedules
     *
     * @Route("/modules/schedules/get-all-not-deleted", methods={"GET"})
     * @return JsonResponse
     */
    public function getAllNotDeletedSchedules(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();

        try{
            $schedulesDtoJsons = [];
            $schedules         = $this->controllers->getMySchedulesController()->getAllNotDeletedSchedules();

            foreach($schedules as $schedule)
            {
                $schedulesDtoJsons[] = ScheduleDTO::fromScheduleEntity($schedule)->toJson();
            }

            $ajaxResponse->setCode(Response::HTTP_OK);;
            $ajaxResponse->setDataBag([
                self::KEY_SCHEDULES_DTO_JSONS => $schedulesDtoJsons,
            ]);

        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * Will return all not deleted schedules
     *
     * @Route("/modules/schedules/delete/{scheduleId}", methods={"GET"})
     * @param string $scheduleId
     * @return JsonResponse
     */
    public function deleteSchedule(string $scheduleId): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();

        try{
            $schedule = $this->controllers->getMySchedulesController()->findOneScheduleById($scheduleId);
            if( empty($schedule) ){

                $message = $this->app->translator->translate('schedules.schedule.message.noScheduleHasBeenFoundForId', [
                    "%id%" => $scheduleId
                ]);
                $ajaxResponse->setMessage($message);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                return $ajaxResponse->buildJsonResponse();
            }

            $message = $this->app->translator->translate('schedules.schedule.message.scheduleHasBeenRemoved');

            $ajaxResponse->setMessage($message);;
            $ajaxResponse->setCode(Response::HTTP_OK);

            $this->app->repositories->deleteById(ScheduleRepository::class, $scheduleId);
        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }

        return $ajaxResponse->buildJsonResponse();
    }

}