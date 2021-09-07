<?php

namespace App\Action\Modules\Schedules;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\DTO\Modules\Schedules\ScheduleDTO;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\MyScheduleReminder;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;


class MySchedulesAction extends AbstractController {
    const KEY_ID                  = 'id';
    const KEY_TITLE               = 'title';
    const KEY_BODY                = 'body';
    const KEY_IS_ALL_DAY          = 'isAllDay';
    const KEY_START               = 'start';
    const KEY_END                 = 'end';
    const KEY_CATEGORY            = 'category';
    const KEY_CALENDAR_ID         = 'calendarId';
    const KEY_LOCATION            = 'location';
    const KEY_REMINDERS           = 'reminders';
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
     * @Route("/my-schedules", name="my_schedules")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request):Response {

        if ( !$request->isXmlHttpRequest() ) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getSchedulesPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $calendarsDataDtoArray = $this->controllers->getMyScheduleCalendarController()->fetchAllNonDeletedCalendarsData();
        $scheduleCalendarForm  = $this->app->forms->scheduleCalendarForm();

        $data = [
            'ajax_render'                    => $ajaxRender,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'calendars_data_dto_array'       => $calendarsDataDtoArray,
            'schedule_calendar_form'         => $scheduleCalendarForm->createView(),
            'page_title'                     => $this->getSchedulesPageTitle(),
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

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
            $body       = $dataArray[self::KEY_BODY];
            $isAllDay   = $dataArray[self::KEY_IS_ALL_DAY];
            $start      = $dataArray[self::KEY_START];
            $end        = $dataArray[self::KEY_END];
            $category   = $dataArray[self::KEY_CATEGORY];
            $calendarId = $dataArray[self::KEY_CALENDAR_ID];
            $location   = $dataArray[self::KEY_LOCATION];
            $reminders  = $dataArray[self::KEY_REMINDERS] ?? ""; // can happen if all reminders are removed in front or none is being sent

            $calendar = $this->controllers->getMyScheduleCalendarController()->findCalendarById($calendarId);
            if( is_null($calendar) ){
                $message = $this->app->translator->translate('schedules.calendar.messages.noCalendarHasBeenFoundForId', [
                    "%id%" => $calendarId
                ]);
                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                return $ajaxResponse->buildJsonResponse();
            }

            $schedule       = new MySchedule();
            $successMessage = $this->app->translator->translate('schedules.schedule.message.scheduleHasBeenCreated');
            if( !empty($scheduleId) ){
                $successMessage = $this->app->translator->translate('schedules.schedule.message.scheduleHasBeenUpdated');
                $schedule       = $this->controllers->getMySchedulesController()->findOneScheduleById($scheduleId);
                if( empty($schedule) ){

                    $message = $this->app->translator->translate('schedules.schedule.message.noScheduleHasBeenFoundForId', [
                        "%id%" => $scheduleId
                    ]);
                    $ajaxResponse->setMessage($message);
                    $ajaxResponse->setSuccess(false);
                    $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);

                    return $ajaxResponse->buildJsonResponse();
                }
            }

            $schedule->setTitle($title);
            $schedule->setBody($body);
            $schedule->setAllDay($isAllDay);
            $schedule->setStart(new DateTime($start));
            $schedule->setEnd(new DateTime($end));
            $schedule->setCategory($category);
            $schedule->setLocation($location);
            $schedule->setCalendar($calendar);

            $this->app->beginTransaction();
            {
                try{

                    $reminderEntitiesToSave = [];
                    if( !empty($reminders) ){
                        $remindersDatesArray = explode(",", $reminders);
                        $remindersDatesArray = array_unique($remindersDatesArray);

                        foreach($schedule->getMyScheduleReminders() as $existingScheduleReminder){
                            $reminderAsDateTimeString = $existingScheduleReminder->getDate()->format("Y-m-d H:i"); // seconds are not delivered from front

                            // handle old existing reminders, decide new to create, remove no longer present on front
                            if( in_array($reminderAsDateTimeString, $remindersDatesArray) ){
                                $reminderEntitiesToSave[]         = $existingScheduleReminder;
                                $keyFromDateInRemindersDatesArray = array_search($reminderAsDateTimeString, $remindersDatesArray);

                                unset($remindersDatesArray[$keyFromDateInRemindersDatesArray]);
                            }else{
                                $this->controllers->getMyScheduleReminderController()->removeReminder($existingScheduleReminder);
                            }
                        }

                        foreach($remindersDatesArray as $reminderDate){
                            $reminder = new MyScheduleReminder();
                            $reminder->setDate(new DateTime($reminderDate));
                            $reminder->setSchedule($schedule);

                            $this->controllers->getMyScheduleReminderController()->saveReminder($reminder);
                            $reminderEntitiesToSave[] = $reminder;
                        }
                    }

                    $schedule->setMyScheduleReminders($reminderEntitiesToSave);
                    $this->controllers->getMySchedulesController()->saveSchedule($schedule);
                }catch(Exception|TypeError $e){
                    $this->app->rollbackTransaction();
                    throw $e;
                }
            }
            $this->app->commitTransaction();

        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);

            $message = $this->app->translator->translate('messages.general.internalServerError');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;

            if($e instanceof NonUniqueResultException){
                $message = $this->app->translator->translate('schedules.reminder.reminderWithThisDateAlreadyExist');
                $code    = Response::HTTP_BAD_REQUEST;
            }

            $ajaxResponse->setCode($code);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }

        // there is no logic to update reminders on dragging - thus simple solution to show information
        if( !empty($schedule->getMyScheduleReminders()) ){
            $successMessage = $this->app->translator->translate('schedules.schedule.message.scheduleHasBeenUpdatedUpdateReminders');
        }

        $ajaxResponse->setMessage($successMessage);
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
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);;

                return $ajaxResponse->buildJsonResponse();
            }

            $message = $this->app->translator->translate('schedules.schedule.message.scheduleHasBeenRemoved');

            $ajaxResponse->setMessage($message);;
            $ajaxResponse->setCode(Response::HTTP_OK);

            $this->app->repositories->deleteById(Repositories::MY_SCHEDULE_REPOSITORY, $scheduleId);
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
     * Will return schedules page title
     *
     * @return string
     */
    private function getSchedulesPageTitle(): string
    {
        return $this->app->translator->translate('schedules.title');
    }

}