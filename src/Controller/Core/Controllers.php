<?php

namespace App\Controller\Core;


use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FilesUploadSettingsController;
use App\Controller\Modules\Achievements\AchievementController;
use App\Controller\Modules\Contacts\MyContactsController;
use App\Controller\Modules\Contacts\MyContactsSettingsController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\Job\MyJobAfterhoursController;
use App\Controller\Modules\Job\MyJobHolidaysPoolController;
use App\Controller\Modules\Job\MyJobSettingsController;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Modules\Passwords\MyPasswordsController;
use App\Controller\Modules\Payments\MyPaymentsBillsController;
use App\Controller\Modules\Payments\MyPaymentsSettingsController;
use App\Controller\Modules\Reports\ReportsController;
use App\Controller\Modules\Schedules\MySchedulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Controller\Modules\Todo\MyTodoElementController;
use App\Controller\Modules\Travels\MyTravelsIdeasController;
use App\Controller\Page\SettingsController;
use App\Controller\Page\SettingsDashboardController;
use App\Controller\Page\SettingsFinancesController;
use App\Controller\Page\SettingsValidationController;
use App\Controller\Page\SettingsViewController;
use App\Controller\System\LockedResourceController;
use App\Controller\System\ModuleController;
use App\Controller\System\SecurityController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class is not allowed in Application() as it will crash due to circular reference as App is used in controllers also
 * Class Controllers
 * @package App\Controller\Utils
 */
class Controllers extends AbstractController {

    /**
     * @var AchievementController|null $achievement_controller
     */
    private $achievement_controller = null;

    /**
     * @var ReportsController $reports_controllers
     */
    private $reports_controllers;

    /**
     * @var MyNotesController $my_notes_controller
     */
    private $my_notes_controller;

    /**
     * @var MyNotesCategoriesController $my_notes_categories_controller
     */
    private $my_notes_categories_controller;

    /**
     * @var LockedResourceController $locked_resource_controller
     */
    private $locked_resource_controller;

    /**
     * @var MyContactsController $my_contact_controller
     */
    private $my_contact_controller;

    /**
     * @var MyContactsSettingsController $my_contact_settings_controller
     */
    private $my_contact_settings_controller;

    /**
     * @var MyImagesController $my_images_controller
     */
    private $my_images_controller;

    /**
     * @var MyFilesController $my_files_controller
     */
    private $my_files_controller;

    /**
     * @var FilesTagsController $files_tags_controller
     */
    private $files_tags_controller;

    /**
     * @var MyJobAfterhoursController $my_job_afterhours_controller
     */
    private $my_job_afterhours_controller;

    /**
     * @var MyJobSettingsController $my_job_settings_controller
     */
    private $my_job_settings_controller;

    /**
     * @var MyPasswordsController $my_passwords_controller
     */
    private $my_passwords_controller;

    /**
     * @var MySchedulesController $my_schedules_controller
     */
    private $my_schedules_controller;

    /**
     * @var MyTravelsIdeasController $my_travels_ideas_controller
     */
    private $my_travels_ideas_controller;

    /**
     * @var MyPaymentsBillsController $my_payments_bills_controller
     */
    private $my_payments_bills_controller;

    /**
     * @var MyPaymentsSettingsController $my_payments_settings_controller;
     */
    private $my_payments_settings_controller;

    /**
     * @var SecurityController $security_controller;
     */
    private $security_controller;

    /**
     * @var SettingsController $settings_controller
     */
    private $settings_controller;

    /**
     * @var SettingsDashboardController $settings_dashboard_controller
     */
    private $settings_dashboard_controller;

    /**
     * @var SettingsFinancesController $settings_finances_controller
     */
    private $settings_finances_controller;

    /**
     * @var SettingsValidationController $settings_validation_controller
     */
    private $settings_validation_controller;

    /**
     * @var SettingsViewController $settings_view_controller
     */
    private $settings_view_controller;

    /**
     * @var FilesUploadSettingsController $files_upload_settings_controller
     */
    private $files_upload_settings_controller;

    /**
     * @var MyIssuesController $my_issues_controller
     */
    private $my_issues_controller;

    /**
     * @var MyJobHolidaysPoolController $my_job_holidays_pool_controller
     */
    private $my_job_holidays_pool_controller;

    /**
     * @var MyTodoController $my_todo_controller
     */
    private $my_todo_controller;

    /**
     * @var MyTodoElementController $my_todo_element_controller
     */
    private $my_todo_element_controller;

    /**
     * @var ModuleController $module_controller
     */
    private $module_controller;

    /**
     * @return MyIssuesController
     */
    public function getMyIssuesController(): MyIssuesController {
        return $this->my_issues_controller;
    }

    /**
     * @return MyImagesController
     */
    public function getMyImagesController(): MyImagesController {
        return $this->my_images_controller;
    }

    /**
     * @return MyContactsController
     */
    public function getMyContactController(): MyContactsController {
        return $this->my_contact_controller;
    }

    /**
     * @return MyContactsSettingsController
     */
    public function getMyContactSettingsController(): MyContactsSettingsController {
        return $this->my_contact_settings_controller;
    }

    /**
     * @return AchievementController|null
     */
    public function getAchievementController(): ?AchievementController {
        return $this->achievement_controller;
    }

    /**
     * @return MyNotesController
     */
    public function getMyNotesController(): MyNotesController {
        return $this->my_notes_controller;
    }

    /**
     * @return MyNotesCategoriesController
     */
    public function getMyNotesCategoriesController(): MyNotesCategoriesController {
        return $this->my_notes_categories_controller;
    }

    /**
     * @return ReportsController
     */
    public function getReportsControllers(): ReportsController {
        return $this->reports_controllers;
    }

    /**
     * @return LockedResourceController
     */
    public function getLockedResourceController(): LockedResourceController {
        return $this->locked_resource_controller;
    }

    /**
     * @return MyFilesController
     */
    public function getMyFilesController(): MyFilesController {
        return $this->my_files_controller;
    }

    /**
     * @return FilesTagsController
     */
    public function getFilesTagsController(): FilesTagsController {
        return $this->files_tags_controller;
    }

    /**
     * @return MyJobAfterhoursController
     */
    public function getMyJobAfterhoursController(): MyJobAfterhoursController {
        return $this->my_job_afterhours_controller;
    }

    /**
     * @return MyJobSettingsController
     */
    public function getMyJobSettingsController(): MyJobSettingsController {
        return $this->my_job_settings_controller;
    }

    /**
     * @return MyPasswordsController
     */
    public function getMyPasswordsController(): MyPasswordsController {
        return $this->my_passwords_controller;
    }

    /**
     * @return MySchedulesController
     */
    public function getMySchedulesController(): MySchedulesController {
        return $this->my_schedules_controller;
    }

    /**
     * @return MyTravelsIdeasController
     */
    public function getMyTravelsIdeasController(): MyTravelsIdeasController {
        return $this->my_travels_ideas_controller;
    }

    /**
     * @return MyPaymentsBillsController
     */
    public function getMyPaymentsBillsController(): MyPaymentsBillsController {
        return $this->my_payments_bills_controller;
    }

    /**
     * @return MyPaymentsSettingsController
     */
    public function getMyPaymentsSettingsController(): MyPaymentsSettingsController {
        return $this->my_payments_settings_controller;
    }

    /**
     * @return SecurityController
     */
    public function getSecurityController(): SecurityController {
        return $this->security_controller;
    }

    /**
     * @return SettingsController
     */
    public function getSettingsController(): SettingsController {
        return $this->settings_controller;
    }

    /**
     * @return SettingsDashboardController
     */
    public function getSettingsDashboardController(): SettingsDashboardController {
        return $this->settings_dashboard_controller;
    }

    /**
     * @return SettingsFinancesController
     */
    public function getSettingsFinancesController(): SettingsFinancesController {
        return $this->settings_finances_controller;
    }

    /**
     * @return SettingsValidationController
     */
    public function getSettingsValidationController(): SettingsValidationController {
        return $this->settings_validation_controller;
    }

    /**
     * @return SettingsViewController
     */
    public function getSettingsViewController(): SettingsViewController {
        return $this->settings_view_controller;
    }

    /**
     * @return FilesUploadSettingsController
     */
    public function getFilesUploadSettingsController(): FilesUploadSettingsController {
        return $this->files_upload_settings_controller;
    }

    /**
     * @return MyJobHolidaysPoolController
     */
    public function getMyJobHolidaysPoolController(): MyJobHolidaysPoolController {
        return $this->my_job_holidays_pool_controller;
    }

    /**
     * @return MyTodoController
     */
    public function getMyTodoController(): MyTodoController {
        return $this->my_todo_controller;
    }

    /**
     * @return MyTodoElementController
     */
    public function getMyTodoElementController(): MyTodoElementController {
        return $this->my_todo_element_controller;
    }

    /**
     * @return ModuleController
     */
    public function getModuleController(): ModuleController {
        return $this->module_controller;
    }

    public function __construct(
        AchievementController         $achievement_controller,
        ReportsController             $reports_controller,
        MyNotesController             $my_notes_controller,
        MyNotesCategoriesController   $my_notes_categories_controller,
        LockedResourceController      $locked_resource_controller,
        MyContactsSettingsController  $my_contact_settings_controller,
        MyContactsController          $my_contact_controller,
        MyImagesController            $my_images_controller,
        MyFilesController             $my_files_controller,
        FilesTagsController           $files_tags_controller,
        MyJobAfterhoursController     $my_job_afterhours_controller,
        MyJobSettingsController       $my_job_settings_controller,
        MyPasswordsController         $my_passwords_controller,
        MySchedulesController         $my_schedules_controller,
        MyTravelsIdeasController      $my_travels_ideas_controller,
        MyPaymentsBillsController     $my_payments_bills_controller,
        MyPaymentsSettingsController  $my_payments_settings_controller,
        SecurityController            $security_controller,
        SettingsController            $settings_controller,
        SettingsDashboardController   $settings_dashboard_controller,
        SettingsFinancesController    $settings_finances_controller,
        SettingsValidationController  $settings_validation_controller,
        SettingsViewController        $settings_view_controller,
        FilesUploadSettingsController $files_upload_settings_controller,
        MyIssuesController            $my_issues_controller,
        MyJobHolidaysPoolController   $my_job_holidays_pool_controller,
        MyTodoController              $my_todo_controller,
        MyTodoElementController       $my_todo_element_controller,
        ModuleController              $module_controller
    ) {
        $this->achievement_controller           = $achievement_controller;
        $this->reports_controllers              = $reports_controller;
        $this->my_notes_controller              = $my_notes_controller;
        $this->my_notes_categories_controller   = $my_notes_categories_controller;
        $this->locked_resource_controller       = $locked_resource_controller;
        $this->my_contact_settings_controller   = $my_contact_settings_controller;
        $this->my_contact_controller            = $my_contact_controller;
        $this->my_images_controller             = $my_images_controller;
        $this->my_files_controller              = $my_files_controller;
        $this->files_tags_controller            = $files_tags_controller;
        $this->my_job_afterhours_controller     = $my_job_afterhours_controller;
        $this->my_job_settings_controller       = $my_job_settings_controller;
        $this->my_passwords_controller          = $my_passwords_controller;
        $this->my_schedules_controller          = $my_schedules_controller;
        $this->my_travels_ideas_controller      = $my_travels_ideas_controller;
        $this->my_payments_bills_controller     = $my_payments_bills_controller;
        $this->my_payments_settings_controller  = $my_payments_settings_controller;
        $this->security_controller              = $security_controller;
        $this->settings_controller              = $settings_controller;
        $this->settings_finances_controller     = $settings_finances_controller;
        $this->settings_dashboard_controller    = $settings_dashboard_controller;
        $this->settings_validation_controller   = $settings_validation_controller;
        $this->settings_view_controller         = $settings_view_controller;
        $this->files_upload_settings_controller = $files_upload_settings_controller;
        $this->my_issues_controller             = $my_issues_controller;
        $this->my_job_holidays_pool_controller  = $my_job_holidays_pool_controller;
        $this->my_todo_controller               = $my_todo_controller;
        $this->my_todo_element_controller       = $my_todo_element_controller;
        $this->module_controller                = $module_controller;
    }

}