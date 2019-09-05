<?php
namespace App\DataFixtures\Providers\Modules;

class JobAfterhours{

    /* Goals */
    const GOAL_FESTIVAL         = 'festival';
    const GOAL_VISING_DOCTOR    = 'visiting doctor';
    const GOAL_VISING_FRIENDS   = 'visiting friends';
    const GOAL_HOLIDAYS         = 'holidays';
    const GOAL_NONE             = '';

    const ALL_GOALS = [
        self::GOAL_FESTIVAL,
        self::GOAL_VISING_DOCTOR,
        self::GOAL_VISING_FRIENDS,
        self::GOAL_HOLIDAYS,
        self::GOAL_NONE,
    ];

    /* Description */
    const DESCRIPTION_FROM_TO   = "Started at: {started_at}, finished at: {finished_at}";

    /* Placeholders */
    const STARTED_AT            = '{started_at}';
    const FINISHED_AT           = '{finished_at}';

}