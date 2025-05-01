<?php

namespace App\Action\Modules\System\Settings\Dashboard;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Modules\ModulesController;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
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

#[Route("/module/system/settings/dashboard/widgets/visibility", name: "module.system.settings.dashboard.widgets.visibility.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_SYSTEM])]
class WidgetsVisibilityAction extends AbstractController {

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
        $setting = $this->settingsLoaderService->getSettingsForDashboard();
        if (empty($setting) || empty($setting->getValue())) {
            return $this->getAllDefaultStateResponse();
        }

        $settingDto = SettingsDashboardDTO::fromJson($setting->getValue());

        if (empty($settingDto->getWidgetSettings()->getWidgetsVisibility())) {
            return $this->getAllDefaultStateResponse();
        }

        $entriesData = [];
        foreach (Setting::ALL_DASHBOARD_WIDGETS as $widgetName) {
            $enabled = true;
            foreach ($settingDto->getWidgetSettings()->getWidgetsVisibility() as $widgetVisibility) {
                if ($widgetVisibility->getName() === $widgetName) {
                    $enabled = $widgetVisibility->isVisible();
                }
            }

            $entriesData[] = [
                'name'    => $widgetName,
                'enabled' => $enabled,
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
        $widgets   = ArrayHandler::get($dataArray, 'widgets');

        $dtos = [];
        foreach ($widgets as $widget) {
            $dto = new SettingsWidgetVisibilityDTO();
            $dto->setName($widget['name']);
            $dto->setIsVisible($widget['enabled']);

            $dtos[] = $dto;
        }

        $this->settingsSaverService->saveSettingsForDashboardWidgetsVisibility($dtos);

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    private function getAllDefaultStateResponse(): JsonResponse
    {
        $entriesData = array_map(
            fn($widgetName) => ['name' => $widgetName, 'enabled' => true],
            Setting::ALL_DASHBOARD_WIDGETS
        );

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }
}