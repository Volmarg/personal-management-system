<?php

namespace App\DataFixtures;

use App\Controller\Core\Application;
use App\DataFixtures\Providers\SettingProvider;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Services\Exceptions\ExceptionValueNotAllowed;
use App\Services\Settings\SettingsLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * @var Application $app
     */
    private $app;

    /**
     * SettingFixtures constructor.
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param ObjectManager $manager
     * @throws ExceptionValueNotAllowed
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {

        $pln_currency_dto  = $this->getPlnCurrency();
        $eur_currency_dto  = $this->getEurCurrency();
        $cash_currency_dto = $this->getCashCurrency();

        $currencies = [
            $pln_currency_dto,
            $eur_currency_dto,
            $cash_currency_dto,
        ];

        $finances_settings_dto = new SettingsFinancesDTO();
        $finances_settings_dto->setSettingsCurrencyDtos($currencies);

        $this->insertCurrenciesIntoDb($finances_settings_dto);
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getPlnCurrency(): SettingsCurrencyDTO {
        $settings_currency_dto = new SettingsCurrencyDTO();
        $settings_currency_dto->setName(SettingProvider::KEY_CURRENCY_NAME_PLN);
        $settings_currency_dto->setIsDefault(1);
        $settings_currency_dto->setMultiplier(1);
        $settings_currency_dto->setSymbol("z\u0142");

        return $settings_currency_dto;
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getEurCurrency(): SettingsCurrencyDTO {
        $settings_currency_dto = new SettingsCurrencyDTO();
        $settings_currency_dto->setName(SettingProvider::KEY_CURRENCY_NAME_EUR);
        $settings_currency_dto->setIsDefault(false);
        $settings_currency_dto->setMultiplier(4.3);
        $settings_currency_dto->setSymbol("\u20ac");

        return $settings_currency_dto;
    }

    /**
     * @throws ExceptionValueNotAllowed
     */
    private function getCashCurrency(): SettingsCurrencyDTO {
        $settings_currency_dto = new SettingsCurrencyDTO();
        $settings_currency_dto->setName(SettingProvider::KEY_CURRENCY_NAME_CASH);
        $settings_currency_dto->setIsDefault(false);
        $settings_currency_dto->setMultiplier(1.01);
        $settings_currency_dto->setSymbol("\ud83d\udcb6");

        return $settings_currency_dto;
    }

    /**
     * This function will insert data into db as RAW text - this is required to prevent escaping slashes
     * Info: this sql is here as it's the only - special use case - not adding it to repository as it's a fixture part
     * @param SettingsFinancesDTO $finances_settings_dto
     * @throws DBALException
     */
    private function insertCurrenciesIntoDb(SettingsFinancesDTO $finances_settings_dto){

        $setting_type = SettingsLoader::SETTING_NAME_FINANCES;
        $json         = $finances_settings_dto->toJson();

            $sql = "
                INSERT IGNORE INTO setting (`id`, `name`, `value`)
                VALUES(2, '{$setting_type}', '{$json}')
            ";

        $this->app->em->getConnection()->executeQuery($sql);
    }
}
