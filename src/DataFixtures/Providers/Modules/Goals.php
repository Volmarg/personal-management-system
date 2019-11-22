<?php
namespace App\DataFixtures\Providers\Modules;


class Goals{

    /*
     * Normal Goals
        'goal name' => [
          'subgoal' => status
        ]
    */

        const GOAL_START_LEARNING_HOW_TO_PLAY_GUITAR = [
            'Start learning how to play guitar' => [
                'Get guitar'                      => false,
                'Check methods for self learning' => true,
                'Check guitars to buy'            => true
            ]
        ];

        const GOAL_GET_MOTORCYCLE = [
            'Get motorcycle' => [
                'Check which ones are available on cat. B' => true,
                'Search for mini choppers on cat. B'       => true,
                'Save founds'                              => false,
            ]
        ];

        const GOAL_LEARN_SYMFONY = [
            'Learn symfony framework' => [
                'Play with demo project'                    => true,
                'Get some online courses'                   => true,
                'Create Your own project on Symfony 4.x'    => true,
            ]
        ];

        const GOAL_FIND_NEW_JOB = [
            'Get new job' => [
                'Learn symfony'                         => true,
                'Learn more about code quality'         => true,
                'Test out the job searching project'    => true,
            ]
        ];

        const GOAL_TRAVEL_TO_FRANCE = [
            'Travel to France' => [
                'Check for any interesting places near border'  => true,
                'Plan the travel date and founds'               => false
            ]
        ];

        const GOAL_BODY_BUILDING = [
            'Body building' => [
                'Get trainings ideas'   => true,
                'Schedule trainings'    => true,
                'Get supplements'       => true,
            ]
        ];

        const GOAL_FINISH_SKYRIM = [
            'Skyrim' => [
                'Finish main story'         => true,
                'Do all the side quests'    => true,
                'Finish all dungeons'       => true,
            ]
        ];

        const ALL_GOALS = [
            self::GOAL_START_LEARNING_HOW_TO_PLAY_GUITAR,
            self::GOAL_GET_MOTORCYCLE,
            self::GOAL_LEARN_SYMFONY,
            self::GOAL_FIND_NEW_JOB,
            self::GOAL_TRAVEL_TO_FRANCE,
            self::GOAL_BODY_BUILDING,
            self::GOAL_FINISH_SKYRIM,
        ];

    /**
     * Payment goals
     */

        const GOAL_PAYMENT_LAPTOP           = 'New laptop';
        const GOAL_PAYMENT_HOLIDAY_TRAVEL   = 'Holiday travel';
        const GOAL_PAYMENT_NEW_SWEATERS     = 'New sweaters';
        const GOAL_PAYMENT_SMARTPHONE       = 'Smartphone';
        const GOAL_PAYMENT_FIXING_CAR       = 'Fixing car';
        const GOAL_PAYMENT_BUYING_GUITAR    = 'Guitar';

        const ALL_PAYMENT_GOALS = [
            self::GOAL_PAYMENT_LAPTOP,
            self::GOAL_PAYMENT_HOLIDAY_TRAVEL,
            self::GOAL_PAYMENT_NEW_SWEATERS,
            self::GOAL_PAYMENT_SMARTPHONE,
            self::GOAL_PAYMENT_FIXING_CAR,
            self::GOAL_PAYMENT_BUYING_GUITAR,
        ];

    /**
     * all
     */

        const KEY_GROUP_GOALS           = 'goals';
        const KEY_GROUP_PAYMENTS_GOALS  = 'payments_goals';

        const ALL = [
            self::KEY_GROUP_PAYMENTS_GOALS  => self::ALL_PAYMENT_GOALS,
            self::KEY_GROUP_GOALS           => self::ALL_GOALS,
        ];

        /**
         * @var boolean $areGroups
         */
        private $areGroups = true;
}