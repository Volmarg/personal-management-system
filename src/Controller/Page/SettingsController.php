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

        $setting_name = $setting->getName();

        if( SettingsLoader::SETTING_NAME_FINANCES !== $setting_name ){
            throw new Exception("Incorrect setting was provided: " . $setting_name);
        }

        $settings_finances_json = $setting->getValue();
        $settings_finances_dto  = SettingsFinancesDTO::fromJson($settings_finances_json);

        return $settings_finances_dto;
    }

}
