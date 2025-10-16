<?php

namespace App\Controller\Page;

use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDto;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDto;
use App\DTO\Settings\SettingsDashboardDto;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsDashboardController extends AbstractController {

    /**
     * This function will build dashboard settings dto based on supplied data, if some is missing then default values will be used
     *
     * @param array|null $arrayOfWidgetsVisibilityDto
     *
     * @return SettingsDashboardDto
     * @throws Exception
     */
    public static function buildDashboardSettingsDto(?array $arrayOfWidgetsVisibilityDto = null): SettingsDashboardDto{

        if( empty($arrayOfWidgetsVisibilityDto) ){
            $arrayOfWidgetsVisibilityDto   = [];
            $arrayOfWidgetsVisibilityDto[] = new SettingsWidgetVisibilityDto();
        }

        $dashboardWidgetsSettingsDto = new SettingsWidgetSettingsDto();
        $dashboardWidgetsSettingsDto->setWidgetVisibility($arrayOfWidgetsVisibilityDto);

        $dashboardSettingsDto = new SettingsDashboardDto();
        $dashboardSettingsDto->setWidgetSettings($dashboardWidgetsSettingsDto);

        return $dashboardSettingsDto;
    }
}
