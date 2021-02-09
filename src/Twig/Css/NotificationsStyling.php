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
     * @param int $daysDiff
     * @return string
     * @throws \Exception
     */
    public function getClassesForSchedulesNotifications(int $daysDiff):string {
        $classes    = '';

        switch($daysDiff) {
            case $daysDiff > 30 && $daysDiff <= 60:
                $classes = 'text-success';
                break;
            case $daysDiff > 14 && $daysDiff <= 30:
                $classes = 'text-warning';
                break;
            case $daysDiff <= 14:
                $classes = 'text-danger';
                break;
        }

        return $classes;
    }
}