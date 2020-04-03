<?php

namespace App\Twig;

use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Utils\Application;
use App\DTO\Settings\Finances\SettingsFinancesDTO;
use App\Entity\Modules\Schedules\MyScheduleType;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Doctrine\DBAL\DBALException;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalVariables extends AbstractExtension {

    const KEY_SCHEDULES_COUNT = 'schedules_count';
    const KEY_SCHEDULES       = 'schedules';

    const CATEGORY_ID  = "category_id";
    const CHILDRENS_ID = "childrens_id";

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyNotesController $my_notes_controller
     */
    private $my_notes_controller;

    public function __construct(Application $app, MyNotesController $my_notes_controller) {
        $this->app = $app;
        $this->my_notes_controller = $my_notes_controller;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getMyNotesCategories', [$this, 'getMyNotesCategories']),
            new TwigFunction('getSchedulesTypes', [$this, 'getSchedulesTypes']),
            new TwigFunction('getSchedulesForNotifications', [$this, 'getSchedulesForNotifications']),
            new TwigFunction('getFinancesCurrenciesDtos', [$this, 'getFinancesCurrenciesDtos']),
        ];
    }

    /**
     * @param bool $filter
     * @return array
     * @throws ExceptionDuplicatedTranslationKey
     * @throws DBALException
     * todo: in future this could me moved to controller once i decide to split everything between actions
     */
    public function getMyNotesCategories($filter = false) {
        $results     = $this->app->repositories->myNotesCategoriesRepository->getCategories();
        $new_results = [];

        if( !$filter ){
            return $results;
        }

        foreach ($results as $key => $result) {
                $category_id = $result[self::CATEGORY_ID];

            if( !$this->my_notes_controller->hasCategoryFamilyVisibleNotes($category_id)){
                unset($results[$key]);
                continue;
            }

            $new_results[$category_id] = $result;

            if (!is_null($results[$key][self::CHILDRENS_ID])) {
                $new_results[$category_id][self::CHILDRENS_ID] = explode(',', $results[$key][self::CHILDRENS_ID]);
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