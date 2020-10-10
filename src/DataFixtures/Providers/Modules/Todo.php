<?php
namespace App\DataFixtures\Providers\Modules;

class Todo{

    /*
     * Todo related to goals module
        'todo name' => [
          'element' => deleted status
        ]
    */

        const TODO_GOAL_START_LEARNING_HOW_TO_PLAY_GUITAR = [
            'Start learning how to play guitar' => [
                'Get guitar'                      => false,
                'Check methods for self learning' => true,
                'Check guitars to buy'            => true
            ]
        ];

        const TODO_GOAL_GET_MOTORCYCLE = [
            'Get motorcycle' => [
                'Check which ones are available on cat. B' => true,
                'Search for mini choppers on cat. B'       => true,
                'Save founds'                              => false,
            ]
        ];

        const TODO_GOAL_LEARN_SYMFONY = [
            'Learn symfony framework' => [
                'Play with demo project'                    => true,
                'Get some online courses'                   => true,
                'Create Your own project on Symfony 4.x'    => true,
            ]
        ];

        const TODO_GOAL_FIND_NEW_JOB = [
            'Get new job' => [
                'Learn symfony'                         => true,
                'Learn more about code quality'         => true,
                'Test out the job searching project'    => true,
            ]
        ];

        const TODO_GOAL_TRAVEL_TO_FRANCE = [
            'Travel to France' => [
                'Check for any interesting places near border'  => true,
                'Plan the travel date and founds'               => false
            ]
        ];

        const TODO_GOAL_BODY_BUILDING = [
            'Body building' => [
                'Get trainings ideas'   => true,
                'Schedule trainings'    => true,
                'Get supplements'       => true,
            ]
        ];

        const TODO_GOAL_FINISH_SKYRIM = [
            'Skyrim' => [
                'Finish main story'         => true,
                'Do all the side quests'    => true,
                'Finish all dungeons'       => true,
            ]
        ];

        const TODO_UPDATE_PROJECT = [
            "Update project" => [
                "PHP 7.4"   => false,
                "Symfony 5" => false,
            ]
        ];

        const TODO_CLEANUP_FRONT_CODE = [
            "Cleanup front code" => [
                "Twig" => false,
                "Js"   => false,
            ]
        ];

        const TODO_ISSUE_DHL = [
            "DHL lost package" => [
                "Write negative opinions wherever it's possible" => false
            ]
        ];

        const ALL_TODO_GOALS = [
            self::TODO_GOAL_START_LEARNING_HOW_TO_PLAY_GUITAR,
            self::TODO_GOAL_GET_MOTORCYCLE,
            self::TODO_GOAL_LEARN_SYMFONY,
            self::TODO_GOAL_FIND_NEW_JOB,
            self::TODO_GOAL_TRAVEL_TO_FRANCE,
            self::TODO_GOAL_BODY_BUILDING,
            self::TODO_GOAL_FINISH_SKYRIM,
        ];

        const ALL_TODO = [
          self::TODO_UPDATE_PROJECT,
          self::TODO_CLEANUP_FRONT_CODE
        ];

        /**
         * Issue Id => todo
         */
        const ALL_TODO_ISSUE = [
            2 => self::TODO_ISSUE_DHL
        ];

}