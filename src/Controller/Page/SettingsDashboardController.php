<?php

namespace App\Controller\Page;

use App\DTO\Settings\Dashboard\SettingsWidgetSettingsDTO;
use App\DTO\Settings\Dashboard\Widget\SettingsWidgetVisibilityDTO;
use App\DTO\Settings\SettingsDashboardDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsDashboardController extends AbstractController {

    /**
     * This function will build dashboard settings dto based on supplied data, if some is missing then default values will be used
     * @param array|null $arrayOfWidgetsVisibilityDto
     *
     * @return SettingsDashboardDTO
     * @throws Exception
     */
    public static function buildDashboardSettingsDto(?array $arrayOfWidgetsVisibilityDto = null): SettingsDashboardDTO{

        if( empty($arrayOfWidgetsVisibilityDto) ){
            $arrayOfWidgetsVisibilityDto   = [];
            $arrayOfWidgetsVisibilityDto[] = new SettingsWidgetVisibilityDTO();
        }

        $dashboardWidgetsSettingsDto = new SettingsWidgetSettingsDTO();
        $dashboardWidgetsSettingsDto->setWidgetVisibility($arrayOfWidgetsVisibilityDto);

        $dashboardSettingsDto = new SettingsDashboardDTO();
        $dashboardSettingsDto->setWidgetSettings($dashboardWidgetsSettingsDto);

        return $dashboardSettingsDto;
    }
}
