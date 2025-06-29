<?php

namespace App\Controller\Core;


use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\ModuleDataController;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Modules\Passwords\MyPasswordsController;
use App\Controller\Modules\Passwords\MyPasswordsGroupsController;
use App\Controller\Modules\Schedules\MyScheduleRemindersController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class is not allowed in Application() as it will crash due to circular reference as App is used in controllers also
 * Class Controllers
 * @package App\Controller\Utils
 */
class Controllers extends AbstractController {


    /**
     * @var MyNotesController $myNotesController
     */
    private $myNotesController;

    /**
     * @var MyNotesCategoriesController $myNotesCategoriesController
     */
    private $myNotesCategoriesController;


    /**
     * @var MyPasswordsController $myPasswordsController
     */
    private $myPasswordsController;

    /**
     * @var MyIssuesController $myIssuesController
     */
    private $myIssuesController;

    /**
     * @var MyPasswordsGroupsController $myPasswordsGroupsController
     */
    private MyPasswordsGroupsController $myPasswordsGroupsController;

    /**
     * @var ModuleDataController $moduleDataController
     */
    private ModuleDataController $moduleDataController;

    /**
     * @var MyScheduleRemindersController $myScheduleReminderController
     */
    private MyScheduleRemindersController $myScheduleReminderController;


    /**
     * @return MyIssuesController
     */
    public function getMyIssuesController(): MyIssuesController {
        return $this->myIssuesController;
    }

    /**
     * @return MyNotesController
     */
    public function getMyNotesController(): MyNotesController {
        return $this->myNotesController;
    }

    /**
     * @return MyNotesCategoriesController
     */
    public function getMyNotesCategoriesController(): MyNotesCategoriesController {
        return $this->myNotesCategoriesController;
    }

    /**
     * @return MyPasswordsController
     */
    public function getMyPasswordsController(): MyPasswordsController {
        return $this->myPasswordsController;
    }


    /**
     * @return MyPasswordsGroupsController
     */
    public function getMyPasswordsGroupsController(): MyPasswordsGroupsController
    {
        return $this->myPasswordsGroupsController;
    }


    /**
     * @return MyScheduleRemindersController
     */
    public function getMyScheduleReminderController(): MyScheduleRemindersController
    {
        return $this->myScheduleReminderController;
    }


    public function __construct(
        MyNotesController             $myNotesController,
        MyNotesCategoriesController   $myNotesCategoriesController,
        MyPasswordsController         $myPasswordsController,
        MyIssuesController            $myIssuesController,
        MyPasswordsGroupsController   $myPasswordsGroupsController,
        ModuleDataController          $moduleDataController,
        MyScheduleRemindersController        $myScheduleReminderController,
    ) {
        $this->myNotesController            = $myNotesController;
        $this->myNotesCategoriesController  = $myNotesCategoriesController;
        $this->myPasswordsController        = $myPasswordsController;
        $this->myIssuesController           = $myIssuesController;
        $this->myPasswordsGroupsController  = $myPasswordsGroupsController;
        $this->moduleDataController         = $moduleDataController;
        $this->myScheduleReminderController         = $myScheduleReminderController;
    }

}