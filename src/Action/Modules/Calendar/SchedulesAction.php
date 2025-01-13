<?php

namespace App\Action\Modules\Calendar;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Schedules\MyScheduleRemindersController;
use App\Entity\Modules\Schedules\MySchedule;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Entity\Modules\Schedules\MyScheduleReminder;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/calendar/schedule", name: "module.calendar.schedule.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_MY_SCHEDULES])]
class SchedulesAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly MyScheduleRemindersController $remindersController
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "new", methods: [Request::METHOD_POST])]
    public function new(Request $request): JsonResponse
    {
        return $this->createOrUpdate($request)->toJsonResponse();
    }

    /**
     * @param MySchedule $schedule
     * @param Request    $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MySchedule $schedule, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $schedule)->toJsonResponse();
    }

    /**
     * @param MySchedule $schedule
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MySchedule $schedule): JsonResponse
    {
        $schedule->setDeleted(true);
        $this->em->persist($schedule);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request         $request
     * @param MySchedule|null $schedule
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MySchedule $schedule = null): BaseResponse
    {
        $isNew = is_null($schedule);
        if ($isNew) {
            $schedule = new MySchedule();
        }

        $dataArray  = RequestService::tryFromJsonBody($request);
        $body       = ArrayHandler::get($dataArray, 'body') ?? '';
        $category   = ArrayHandler::get($dataArray, 'category');
        $end        = ArrayHandler::get($dataArray, 'end');
        $start      = ArrayHandler::get($dataArray, 'start');
        $title      = ArrayHandler::get($dataArray, 'title');
        $location   = ArrayHandler::get($dataArray, 'location');
        $isAllDay   = ArrayHandler::get($dataArray, 'isAllDay');
        $calendarId = ArrayHandler::get($dataArray, 'calendarId');
        $reminders  = ArrayHandler::get($dataArray, 'reminders', true, ''); // can happen if all reminders are removed in front or none is being sent

        $calendar = $this->em->find(MyScheduleCalendar::class, $calendarId);
        if (is_null($calendar)) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.calendar.schedules.save.noMatchingCalendarFound'));
        }

        $schedule->setBody($body);
        $schedule->setCategory($category);
        $schedule->setEnd(new DateTime($end));
        $schedule->setStart(new DateTime($start));
        $schedule->setTitle($title);
        $schedule->setLocation($location);
        $schedule->setAllDay($isAllDay);
        $schedule->setCalendar($calendar);

        $this->em->beginTransaction();
        try {
            $this->em->persist($schedule);
            $this->em->flush();

            $this->handleReminders($reminders, $schedule);
            $this->em->persist($schedule);
            $this->em->flush();
        } catch (Exception $e) {
            $this->em->rollback();
            throw $e;
        }
        $this->em->commit();

        return BaseResponse::buildOkResponse();
    }

    /**
     * This function is almost 1:1 copy and paste from legacy code
     *
     * @param string     $remindersData
     * @param MySchedule $schedule
     *
     * @throws Exception
     */
    private function handleReminders(string $remindersData, MySchedule $schedule): void {
        if (empty($remindersData)) {
            foreach ($schedule->getMyScheduleReminders() as $reminder) {
                $this->em->remove($reminder);
            }
            return;
        }

        $reminderEntities = [];
        $remindersDatesArray = explode(",", $remindersData);
        $remindersDatesArray = array_unique($remindersDatesArray);

        foreach ($schedule->getMyScheduleReminders() as $existingScheduleReminder) {
            $reminderDateTimeString = $existingScheduleReminder->getDate()->format("Y-m-d H:i"); // seconds are not delivered from front

            // handle old existing reminders, decide new to create, remove no longer present on front
            if (in_array($reminderDateTimeString, $remindersDatesArray)) {
                $reminderEntities[] = $existingScheduleReminder;
                $reminderKey        = array_search($reminderDateTimeString, $remindersDatesArray);

                unset($remindersDatesArray[$reminderKey]);
                continue;
            }

            $this->remindersController->removeReminder($existingScheduleReminder);
        }

        foreach ($remindersDatesArray as $reminderDate) {
            $reminder = new MyScheduleReminder();
            $reminder->setDate(new DateTime($reminderDate));
            $reminder->setSchedule($schedule);
            $reminderEntities[] = $reminder;
        }

        foreach ($reminderEntities as $entity) {
            $this->em->persist($entity);
        }

        $schedule->setMyScheduleReminders($reminderEntities);
    }

}