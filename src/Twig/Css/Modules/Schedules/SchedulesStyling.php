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
     * @param \DateTime $scheduleDate
     * @return string
     * @throws \Exception
     */
    public function getClassesForSchedulesTable(?\DateTime $scheduleDate):string {
        $classes    = '';

        if( is_null($scheduleDate) ){
            return $classes;
        }

        $currDate = new \DateTime();
        $daysDiff = (int)$currDate->diff($scheduleDate)->format('%r%a');

        switch($daysDiff) {
            case $daysDiff > 30 && $daysDiff <= 60:
                $classes = 'table-success';
                break;
            case $daysDiff > 14 && $daysDiff <= 30:
                $classes = 'table-warning';
                break;
            case $daysDiff <= 14:
                $classes = 'table-danger';
                break;
        }

        return $classes;
    }

    /**
     * @param int $daysDiff
     * @return string
     */
    public function getClassesForSchedulesWidget(int $daysDiff):string {
        $classes    = '';

        switch($daysDiff) {
            case $daysDiff > 30 && $daysDiff <= 60:
                $classes = 'badge-success';
                break;
            case $daysDiff > 14 && $daysDiff <= 30:
                $classes = 'badge-warning';
                break;
            case $daysDiff <= 14:
                $classes = 'badge-danger';
                break;
        }

        return $classes;
    }


}