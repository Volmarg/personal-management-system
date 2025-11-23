<?php

namespace App\Action\Modules\System;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Schedules\MySchedule;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\TypeProcessor\TextHandler;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/system/notification", name: "module.system.notification.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_SYSTEM])]
class NotificationAction extends AbstractController {

    private const int TEXT_MAX_LEN = 40;

    // keep in mind that types values are also used on front in notifications
    private const string TYPE_INFO = "info";
    private const string TYPE_WARN = "warn";
    private const string TYPE_ERROR = "error";
    private const string TYPE_CRITICAL = "critical";

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface    $translator
    ) {
    }

    /**
     *
     * @return JsonResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $entriesData  = [];
        $scheduleDtos = $this->entityManager->getRepository(MySchedule::class)->getIncomingSchedulesInformationInDays(60);
        foreach ($scheduleDtos as $dto) {
            // keep in mind that these days values are also used on front in schedule to determine the color, keep the values in sync
            $type = self::TYPE_INFO;
            if ($dto->getDaysDiff() > 14 && $dto->getDaysDiff() < 30) {
                $type = self::TYPE_WARN;
            }

            if ($dto->getDaysDiff() > 0 && $dto->getDaysDiff() < 14) {
                $type = self::TYPE_ERROR;
            }

            if ($dto->getDaysDiff() < 0) {
                $type = self::TYPE_CRITICAL;
            }

            $daysPart = $this->translator->trans('module.system.notification.type.schedule.msg.inDays', ["{{days}}" => $dto->getDaysDiff()]);
            $entriesData[] = [
                "group" => "schedule",
                "type"  => $type,
                "text"  => TextHandler::shortenWithDots($dto->getTitle(), self::TEXT_MAX_LEN) . " (" . $daysPart . ")"
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

}