<?php

namespace App\Twig\Core;

use App\Controller\Core\Application;
use Doctrine\DBAL\Exception;
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
     *
     * @param int $daysMaxOffset
     * @return array
     * @throws Exception
     */
    public function getSchedulesForNotifications(int $daysMaxOffset): array
    {
        $schedules = $this->app->repositories->myScheduleRepository->getIncomingSchedulesInformationInDays($daysMaxOffset);

        $data = [
            self::KEY_SCHEDULES       => $schedules,
            self::KEY_SCHEDULES_COUNT => count($schedules),
        ];

        return $data;
    }


}