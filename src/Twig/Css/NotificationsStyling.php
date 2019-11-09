<?php

namespace App\Twig\Css;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationsStyling extends AbstractExtension {

    public function getFunctions() {
        return [
            new TwigFunction('getClassesForSchedulesNotifications', [$this, 'getClassesForSchedulesNotifications']),
        ];
    }

    /**
     * @param int $days_diff
     * @return string
     * @throws \Exception
     */
    public function getClassesForSchedulesNotifications(int $days_diff):string {
        $classes    = '';

        switch($days_diff) {
            case $days_diff > 30 && $days_diff <= 60:
                $classes = 'text-success';
                break;
            case $days_diff > 14 && $days_diff <= 30:
                $classes = 'text-warning';
                break;
            case $days_diff <= 14:
                $classes = 'text-danger';
                break;
        }

        return $classes;
    }
}