<?php

namespace App\Twig\Core;

use App\Controller\Core\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Notifications extends AbstractExtension {

    const KEY_SCHEDULES_COUNT = 'schedules_count';
    const KEY_SCHEDULES       = 'schedules';

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getSchedulesForNotifications', [$this, 'getSchedulesForNotifications']),
        ];
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


}