<?php

namespace App\Controller\Page;

use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\SettingValidationDTO;
use App\Services\Settings\SettingsLoader;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SettingsValidationController extends AbstractController {

    /**
     * @var \App\Services\Core\Translator $translator
     */
    private $translator;

    /**
     * @var SettingsLoader $settings_loader
     */
    private $settings_loader;

    public function __construct(Translator $translator, SettingsLoader $settings_loader) {
        $this->settings_loader = $settings_loader;
        $this->translator      = $translator;
    }

    /**
     * This function checks if there are no duplicated settings jsons
     * Since more that one type of dto must be validated - the second param - key will be used as getter
     * @param $dto
     * @return SettingValidationDTO
     * @throws Exception
     */
    public function isValueByKeyUnique($dto): SettingValidationDTO {
        $message = $this->translator->translate("messages.SettingValidationDTO.success");

        $setting_validation_dto = new SettingValidationDTO();
        $setting_validation_dto->setMessage($message);
        $setting_validation_dto->setIsValid(true);

        $dto_class = get_class($dto);

        switch( $dto_class ){
            case SettingsCurrencyDTO::class:
                $setting       = $this->settings_loader->getSettingsForFinances();
                $validated_key = SettingsCurrencyDTO::KEY_NAME;

                if( empty($setting) ){
                    return $setting_validation_dto;
                }

                $settings_finances_dto = SettingsController::buildFinancesSettingsDtoFromSetting($setting);
                $saved_dtos_in_setting = $settings_finances_dto->getSettingsCurrencyDtos();

                break;
            default:
                $message = $this->translator->translate("exceptions.dtoValidation.unsupportedSetting");
                throw new Exception($message . $dto_class);
        }

        $method_name   = "get" . $validated_key;
        $new_dto_value = $dto->{$method_name}();

        foreach( $saved_dtos_in_setting as $saved_dto_in_setting ){
            $saved_dto_value = $saved_dto_in_setting->{$method_name}();

            if( $new_dto_value === $saved_dto_value) {
                $message = $this->translator->translate("messages.failure.SettingValidationDTO.duplicatedValue") . ": " . $validated_key;

                $setting_validation_dto->setMessage($message);
                $setting_validation_dto->setIsValid(false);

                return $setting_validation_dto;
            }
        }

        return $setting_validation_dto;
    }

}
