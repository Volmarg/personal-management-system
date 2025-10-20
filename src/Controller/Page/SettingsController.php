<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\Entity\Setting;
use App\Services\Settings\SettingsLoader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsController extends AbstractController {

    /**
     * @param Setting|null $setting
     *
     * @return SettingsFinancesDto|null
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromSetting(?Setting $setting): ?SettingsFinancesDto {

        if( empty($setting) ){
            return null;
        }

        $settingName = $setting->getName();

        if( SettingsLoader::SETTING_NAME_FINANCES !== $settingName ){
            throw new Exception("Incorrect setting was provided: " . $settingName);
        }

        $settingsFinancesJson = $setting->getValue();
        $settingsFinancesDto  = SettingsFinancesDto::fromJson($settingsFinancesJson);

        return $settingsFinancesDto;
    }

}
