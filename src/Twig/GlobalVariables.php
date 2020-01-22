<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig;

use App\Controller\Utils\Application;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Entity\Modules\Schedules\MyScheduleType;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalVariables extends AbstractExtension {

    const KEY_SCHEDULES_COUNT = 'schedules_count';
    const KEY_SCHEDULES       = 'schedules';

    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getMyNotesCategories', [$this, 'getMyNotesCategories']),
            new TwigFunction('getSchedulesTypes', [$this, 'getSchedulesTypes']),
            new TwigFunction('getSchedulesForNotifications', [$this, 'getSchedulesForNotifications']),
            new TwigFunction('getFinancesCurrenciesDtos', [$this, 'getFinancesCurrenciesDtos']),
        ];
    }

    public function getMyNotesCategories($all = false) {
        $results = $this->app->repositories->myNotesRepository->getCategories($all);
        $new_results = [];

        foreach ($results as $key => $result) {
            $new_results[$result['category_id']] = $result;

            if (!is_null($results[$key]['childrens_id'])) {
                $new_results[$result['category_id']]['childrens_id'] = explode(',', $results[$key]['childrens_id']);
            }
        }

        return $new_results;
    }

    /**
     * @return MyScheduleType[]
     */
    public function getSchedulesTypes():array {
        return $this->app->repositories->myScheduleTypeRepository->getAllTypes();
    }

    /**
     * This function must exists in twig as this is used for overall top-bar
     * @param int $days_max_offset
     * @return mixed[]
     */
    public function getSchedulesForNotifications(int $days_max_offset){
        $schedules = $this->app->repositories->myScheduleRepository->getIncomingSchedulesInDays($days_max_offset);

        $data = [
            self::KEY_SCHEDULES       => $schedules,
            self::KEY_SCHEDULES_COUNT => count($schedules),
        ];

        return $data;
    }

    /**
     * @throws Exception
     * @return SettingsFinancesDTO[]
     */
    public function getFinancesCurrenciesDtos(): array {
        $finances_currencies_dtos = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();
        return $finances_currencies_dtos;
    }
}