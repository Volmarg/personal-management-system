<?php

namespace App\Action\Modules\System\Settings\Notification;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\DTO\Settings\Notifications\ConfigDTO;
use App\DTO\Settings\SettingNotificationDTO;
use App\Entity\Setting;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\Settings\SettingsLoader;
use App\Services\Settings\SettingsSaver;
use App\Services\TypeProcessor\ArrayHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/system/settings/notifications/config", name: "module.system.settings.notifications.config.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_SYSTEM])]
class ConfigAction extends AbstractController {

    /**
     * @param SettingsSaver  $settingsSaverService
     * @param SettingsLoader $settingsLoaderService
     */
    public function __construct(
        private readonly SettingsSaver  $settingsSaverService,
        private readonly SettingsLoader $settingsLoaderService,
    ) {
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $setting = $this->settingsLoaderService->getSettingsForNotifications();
        if (empty($setting) || empty($setting->getValue())) {
            return $this->getAllDefaultStateResponse();
        }

        $entriesData = [];
        $dto         = SettingNotificationDTO::fromJson($setting->getValue());
        foreach ($dto->getConfig() as $configDto) {
            $entriesData[] = [
                ConfigDTO::KEY_NAME                => $configDto->getName(),
                ConfigDTO::KEY_VALUE               => $configDto->getValue(),
                ConfigDTO::KEY_ACTIVE_FOR_REMINDER => $configDto->isActiveForReminder(),
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
    #[Route("", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Request $request): JsonResponse
    {
        $dataArray = RequestService::tryFromJsonBody($request);
        $configs   = ArrayHandler::get($dataArray, 'configs');

        $dtos = [];
        foreach ($configs as $configData) {
            $configDto = new ConfigDTO();
            $configDto->setName($configData[ConfigDTO::KEY_NAME]);
            $configDto->setValue($configData[ConfigDTO::KEY_VALUE]);
            $configDto->setActiveForReminder($configData[ConfigDTO::KEY_ACTIVE_FOR_REMINDER]);

            $dtos[] = $configDto;
        }

        $this->settingsSaverService->saveNotificationsConfigSettings($dtos);

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    private function getAllDefaultStateResponse(): JsonResponse
    {
        $entriesData = array_map(
            fn($configName) => [
                ConfigDTO::KEY_NAME                => $configName,
                ConfigDTO::KEY_ACTIVE_FOR_REMINDER => false,
                ConfigDTO::KEY_VALUE               => '',
            ],
            Setting::ALL_NOTIFICATION_CONFIGS
        );

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }
}