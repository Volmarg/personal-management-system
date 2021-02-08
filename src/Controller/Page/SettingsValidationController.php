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
     * @var SettingsLoader $settingsLoader
     */
    private $settingsLoader;

    public function __construct(Translator $translator, SettingsLoader $settingsLoader) {
        $this->settingsLoader = $settingsLoader;
        $this->translator     = $translator;
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

        $settingValidationDto = new SettingValidationDTO();
        $settingValidationDto->setMessage($message);
        $settingValidationDto->setIsValid(true);

        $dtoClass = get_class($dto);

        switch( $dtoClass ){
            case SettingsCurrencyDTO::class:
                $setting      = $this->settingsLoader->getSettingsForFinances();
                $validatedKey = SettingsCurrencyDTO::KEY_NAME;

                if( empty($setting) ){
                    return $settingValidationDto;
                }

                $settingsFinancesDto = SettingsController::buildFinancesSettingsDtoFromSetting($setting);
                $savedDtosInSetting  = $settingsFinancesDto->getSettingsCurrencyDtos();

                break;
            default:
                $message = $this->translator->translate("exceptions.dtoValidation.unsupportedSetting");
                throw new Exception($message . $dtoClass);
        }

        $methodName   = "get" . $validatedKey;
        $newDtoValue = $dto->{$methodName}();

        foreach( $savedDtosInSetting as $savedDtoInSetting ){
            $savedDtoValue = $savedDtoInSetting->{$methodName}();

            if( $newDtoValue === $savedDtoValue) {
                $message = $this->translator->translate("messages.failure.SettingValidationDTO.duplicatedValue") . ": " . $validatedKey;

                $settingValidationDto->setMessage($message);
                $settingValidationDto->setIsValid(false);

                return $settingValidationDto;
            }
        }

        return $settingValidationDto;
    }

}
