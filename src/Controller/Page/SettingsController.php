<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Entity\Setting;
use App\Services\Settings\SettingsLoader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsController extends AbstractController {

    const KEY_BEFORE_UPDATE_STATE = "before_update_state";

    /**
     * @param Setting|null $setting
     * @return SettingsFinancesDTO|null
     * @throws Exception
     */
    public static function buildFinancesSettingsDtoFromSetting(?Setting $setting): ?SettingsFinancesDTO {

        if( empty($setting) ){
            return null;
        }

        $settingName = $setting->getName();

        if( SettingsLoader::SETTING_NAME_FINANCES !== $settingName ){
            throw new Exception("Incorrect setting was provided: " . $settingName);
        }

        $settingsFinancesJson = $setting->getValue();
        $settingsFinancesDto  = SettingsFinancesDTO::fromJson($settingsFinancesJson);

        return $settingsFinancesDto;
    }

}
