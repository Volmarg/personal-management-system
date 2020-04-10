<?php

namespace App\Twig\Css\Modules\Schedules;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SchedulesStyling extends AbstractExtension {

    public function getFunctions() {
        return [
            new TwigFunction('getClassesForSchedulesWidget', [$this, 'getClassesForSchedulesWidget']),
            new TwigFunction('getClassesForSchedulesTable', [$this, 'getClassesForSchedulesTable']),
        ];
    }

    /**
     * @param \DateTime $schedule_date
     * @return string
     * @throws \Exception
     */
    public function getClassesForSchedulesTable(?\DateTime $schedule_date):string {
        $classes    = '';

        if( is_null($schedule_date) ){
            return $classes;
        }

        $currDate   = new \DateTime();
        $days_diff  = (int)$currDate->diff($schedule_date)->format('%a');

        switch($days_diff) {
            case $days_diff > 30 && $days_diff <= 60:
                $classes = 'table-success';
                break;
            case $days_diff > 14 && $days_diff <= 30:
                $classes = 'table-warning';
                break;
            case $days_diff <= 14:
                $classes = 'table-danger';
                break;
        }

        return $classes;
    }

    /**
     * @param int $days_diff
     * @return string
     */
    public function getClassesForSchedulesWidget(int $days_diff):string {
        $classes    = '';

        switch($days_diff) {
            case $days_diff > 30 && $days_diff <= 60:
                $classes = 'badge-success';
                break;
            case $days_diff > 14 && $days_diff <= 30:
                $classes = 'badge-warning';
                break;
            case $days_diff <= 14:
                $classes = 'badge-danger';
                break;
        }

        return $classes;
    }


}