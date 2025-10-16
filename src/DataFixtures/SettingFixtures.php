<?php

namespace App\DataFixtures;

use App\DataFixtures\Providers\SettingProvider;
use App\DTO\Settings\Finances\SettingsCurrencyDto;
use App\DTO\Settings\Finances\SettingsFinancesDto;
use App\Services\Exceptions\ExceptionValueNotAllowed;
use App\Services\Settings\SettingsLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;

class SettingFixtures extends Fixture
{
    const ENABLED               = true;
    const SALT                  = NULL;
    const LAST_LOGIN            = NULL;
    const CONFIRMATION_TOKEN    = NULL;
    const PASSWORD_REQUESTED_AT = NULL;
    const AVATAR                = NULL;
    const NICKNAME              = NULL;
    const PASSWORD              = '$2y$13$.VnnN5tJ8evchXidKXZnZePceiQ1FFzr/9SLg8DNGyeKpbnqBelDW'; #admin
    const ROLES                 = 'ROLE_SUPER_ADMIN';
    const USERNAME              = 'admin';

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param ObjectManager $manager
     * @throws ExceptionValueNotAllowed
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        $plnCurrencyDto  = $this->getPlnCurrency();
        $eurCurrencyDto  = $this->getEurCurrency();
        $cashCurrencyDto = $this->getCashCurrency();

        $currencies = [
            $plnCurrencyDto,
            $eurCurrencyDto,
            $cashCurrencyDto,
        ];

        $financesSettingsDto = new SettingsFinancesDto();
        $financesSettingsDto->setSettingsCurrencyDtos($currencies);

        $this->insertCurrenciesIntoDb($financesSettingsDto);
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getPlnCurrency(): SettingsCurrencyDto {
        $settingsCurrencyDto = new SettingsCurrencyDto();
        $settingsCurrencyDto->setName(SettingProvider::KEY_CURRENCY_NAME_PLN);
        $settingsCurrencyDto->setIsDefault(1);
        $settingsCurrencyDto->setMultiplier(1);
        $settingsCurrencyDto->setSymbol("z\u0142");

        return $settingsCurrencyDto;
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getEurCurrency(): SettingsCurrencyDto {
        $settingsCurrencyDto = new SettingsCurrencyDto();
        $settingsCurrencyDto->setName(SettingProvider::KEY_CURRENCY_NAME_EUR);
        $settingsCurrencyDto->setIsDefault(false);
        $settingsCurrencyDto->setMultiplier(4.3);
        $settingsCurrencyDto->setSymbol("\u20ac");

        return $settingsCurrencyDto;
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getCashCurrency(): SettingsCurrencyDto {
        $settingsCurrencyDto = new SettingsCurrencyDto();
        $settingsCurrencyDto->setName(SettingProvider::KEY_CURRENCY_NAME_CASH);
        $settingsCurrencyDto->setIsDefault(false);
        $settingsCurrencyDto->setMultiplier(1.01);
        $settingsCurrencyDto->setSymbol("\ud83d\udcb6");

        return $settingsCurrencyDto;
    }

    /**
     * This function will insert data into db as RAW text - this is required to prevent escaping slashes
     * Info: this sql is here as it's the only - special use case - not adding it to repository as it's a fixture part
     *
     * @param SettingsFinancesDto $financesSettingsDto
     *
     * @throws DBALException
     */
    private function insertCurrenciesIntoDb(SettingsFinancesDto $financesSettingsDto){

        $settingType = SettingsLoader::SETTING_NAME_FINANCES;
        $json        = $financesSettingsDto->toJson();

            $sql = "
                INSERT IGNORE INTO setting (`id`, `name`, `value`)
                VALUES(2, '{$settingType}', '{$json}')
            ";

        $this->em->getConnection()->executeQuery($sql);
    }
}
