<?php

namespace App\Services\Settings;

use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\Entity\Setting;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsService extends AbstractController {

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
