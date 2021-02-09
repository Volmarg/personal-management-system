<?php


namespace App\Twig\Page;


use App\Controller\Core\Application;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Settings extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(
        Application $app
    ) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getFinancesCurrenciesDtos', [$this, 'getFinancesCurrenciesDtos']),
        ];
    }

    /**
     * @throws Exception
     * @return SettingsFinancesDTO[]
     */
    public function getFinancesCurrenciesDtos(): array {
        $financesCurrenciesDtos = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();
        return $financesCurrenciesDtos;
    }

}