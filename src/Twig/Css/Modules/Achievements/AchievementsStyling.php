<?php

namespace App\Twig\Css\Modules\Achievements;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AchievementsStyling extends AbstractExtension {

    const SIMPLE        = 'simple';
    const MEDIUM        = 'medium';
    const HARD          = 'hard';
    const HARDCORE      = 'hardcore';

    const CLASS_ALERT           = 'alert';
    const CLASS_ALERT_SUCCESS   = 'alert-success';
    const CLASS_ALERT_WARNING   = 'alert-warning';
    const CLASS_ALERT_SECONDARY = 'alert-secondary';
    const CLASS_ALERT_DANGER    = 'alert-danger';

    public function getFunctions() {
        return [
            new TwigFunction('getClassForAchievementType', [$this, 'getClassForAchievementType']),
        ];
    }

    /**
     * @param string $achievementTyp
     * @return string
     */
    public function getClassForAchievementType(string $achievementTyp) {
        $class = '';

        switch (strtolower($achievementTyp)) {
            case static::SIMPLE:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_SUCCESS;
                break;
            case static::MEDIUM:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_WARNING;
                break;
            case static::HARD:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_SECONDARY;
                break;
            case static::HARDCORE:
                $class = static::CLASS_ALERT . ' ' . static::CLASS_ALERT_DANGER;
                break;
        }

        return $class;
    }

}