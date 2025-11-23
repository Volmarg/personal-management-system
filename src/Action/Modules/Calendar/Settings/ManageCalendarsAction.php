<?php

namespace App\Action\Modules\Calendar\Settings;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/calendar/manage", name: "module.calendar.manage.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_MY_SCHEDULES])]
class ManageCalendarsAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $calendars = $this->em->getRepository(MyScheduleCalendar::class)->findBy(['deleted' => false]);

        $entriesData = [];
        foreach ($calendars as $calendar) {
            $schedules = [];
            foreach ($calendar->getSchedules() as $schedule) {
                if ($schedule->isDeleted()) {
                    continue;
                }

                $schedules[] = $schedule->asFrontendData();
            }

            $entriesData[] = [
                'id'        => $calendar->getId(),
                'name'      => $calendar->getName(),
                'color'     => "#{$calendar->getBackgroundColor()}",
                'schedules' => $schedules,
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
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
     * @param MyScheduleCalendar $calendar
     * @param Request            $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyScheduleCalendar $calendar, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $calendar)->toJsonResponse();
    }

    /**
     * @param MyScheduleCalendar $group
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyScheduleCalendar $group): JsonResponse
    {
        $group->setDeleted(true);
        $this->em->persist($group);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                 $request
     * @param MyScheduleCalendar|null $calendar
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyScheduleCalendar $calendar = null): BaseResponse
    {
        $isNew = is_null($calendar);
        if ($isNew) {
            $calendar = new MyScheduleCalendar();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $name      = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $color     = ArrayHandler::get($dataArray, 'color', allowEmpty: false);

        $entity = $this->em->getRepository(MyScheduleCalendar::class)->findOneBy(['name' => $name]);
        // only allow saving already existing entity with unchanged name
        if ((!is_null($entity) && $isNew) || (!$isNew && $calendar->getName() !== $name && !is_null($entity)) ) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.calendar.settings.manageCalendars.createdUpdate.nameExist'));
        }

        $calendar->setName($name);
        $calendar->setColor('WHITE'); // this is on purpose

        $calendar->setBorderColor($color);
        $calendar->setBackgroundColor($color);
        $calendar->setDragBackgroundColor($color);
        $calendar->setIcon($color);

        $this->em->persist($calendar);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}