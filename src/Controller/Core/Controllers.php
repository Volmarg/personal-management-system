<?php

namespace App\Controller\Core;


use App\Controller\Files\FilesTagsController;
use App\Controller\Files\FilesUploadSettingsController;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\Contacts\MyContactsController;
use App\Controller\Modules\Contacts\MyContactsSettingsController;
use App\Controller\Modules\Contacts\MyContactTypeController;
use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Modules\Goals\GoalsListController;
use App\Controller\Modules\Goals\GoalsPaymentsController;
use App\Controller\Modules\Images\MyImagesController;
use App\Controller\Modules\Issues\MyIssueContactController;
use App\Controller\Modules\Issues\MyIssueProgressController;
use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\Modules\Job\MyJobHolidaysPoolController;
use App\Controller\Modules\Job\MyJobSettingsController;
use App\Controller\Modules\ModuleDataController;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Modules\Notes\MyNotesController;
use App\Controller\Modules\Passwords\MyPasswordsController;
use App\Controller\Modules\Passwords\MyPasswordsGroupsController;
use App\Controller\Modules\Payments\MyPaymentsBillsController;
use App\Controller\Modules\Payments\MyPaymentsBillsItemsController;
use App\Controller\Modules\Payments\MyPaymentsIncomeController;
use App\Controller\Modules\Payments\MyPaymentsMonthlyController;
use App\Controller\Modules\Payments\MyPaymentsOwedController;
use App\Controller\Modules\Payments\MyPaymentsProductsController;
use App\Controller\Modules\Payments\MyPaymentsSettingsController;
use App\Controller\Modules\Payments\MyRecurringPaymentsMonthlyController;
use App\Controller\Modules\Reports\ReportsController;
use App\Controller\Modules\Schedules\MyScheduleRemindersController;
use App\Controller\Modules\Schedules\MySchedulesController;
use App\Controller\Modules\Todo\MyTodoController;
use App\Controller\Modules\Todo\MyTodoElementController;
use App\Controller\Modules\Travels\MyTravelsIdeasController;
use App\Controller\Modules\Video\MyVideoController;
use App\Controller\Page\SettingsController;
use App\Controller\Page\SettingsDashboardController;
use App\Controller\Page\SettingsFinancesController;
use App\Controller\Page\SettingsValidationController;
use App\Controller\Page\SettingsViewController;
use App\Controller\System\LockedResourceController;
use App\Controller\System\ModuleController;
use App\Controller\System\SecurityController;
use App\Controller\UserController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This class is not allowed in Application() as it will crash due to circular reference as App is used in controllers also
 * Class Controllers
 * @package App\Controller\Utils
 */
class Controllers extends AbstractController {

    /**
     * @var ReportsController $reportsControllers
     */
    private $reportsControllers;

    /**
     * @var MyNotesController $myNotesController
     */
    private $myNotesController;

    /**
     * @var MyNotesCategoriesController $myNotesCategoriesController
     */
    private $myNotesCategoriesController;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private $lockedResourceController;

    /**
     * @var MyContactsController $myContactController
     */
    private $myContactController;

    /**
     * @var MyContactsSettingsController $myContactSettingsController
     */
    private $myContactSettingsController;

    /**
     * @var MyImagesController $myImagesController
     */
    private $myImagesController;

    /**
     * @var MyFilesController $myFilesController
     */
    private $myFilesController;

    /**
     * @var FilesTagsController $filesTagsController
     */
    private $filesTagsController;

    /**
     * @var MyJobSettingsController $myJobSettingsController
     */
    private $myJobSettingsController;

    /**
     * @var MyPasswordsController $myPasswordsController
     */
    private $myPasswordsController;

    /**
     * @var MySchedulesController $mySchedulesController
     */
    private $mySchedulesController;

    /**
     * @var MyTravelsIdeasController $myTravelsIdeasController
     */
    private $myTravelsIdeasController;

    /**
     * @var MyPaymentsBillsController $myPaymentsBillsController
     */
    private $myPaymentsBillsController;

    /**
     * @var MyPaymentsSettingsController $myPaymentsSettingsController;
     */
    private $myPaymentsSettingsController;

    /**
     * @var SecurityController $securityController;
     */
    private $securityController;

    /**
     * @var SettingsController $settingsController
     */
    private $settingsController;

    /**
     * @var SettingsDashboardController $settingsDashboardController
     */
    private $settingsDashboardController;

    /**
     * @var SettingsFinancesController $settingsFinancesController
     */
    private $settingsFinancesController;

    /**
     * @var SettingsValidationController $settingsValidationController
     */
    private $settingsValidationController;

    /**
     * @var SettingsViewController $settingsViewController
     */
    private $settingsViewController;

    /**
     * @var FilesUploadSettingsController $filesUploadSettingsController
     */
    private $filesUploadSettingsController;

    /**
     * @var MyIssuesController $myIssuesController
     */
    private $myIssuesController;

    /**
     * @var MyJobHolidaysPoolController $myJobHolidaysPoolController
     */
    private $myJobHolidaysPoolController;

    /**
     * @var MyTodoController $myTodoController
     */
    private $myTodoController;

    /**
     * @var MyTodoElementController $myTodoElementController
     */
    private $myTodoElementController;

    /**
     * @var ModuleController $moduleController
     */
    private $moduleController;

    /**
     * @var GoalsListController $goalsListController
     */
    private $goalsListController;

    /**
     * @var UserController $userController
     */
    private UserController $userController;

    /**
     * @var MyVideoController $myVideoController
     */
    private MyVideoController $myVideoController;

    /**
     * @var MyContactTypeController $myContactTypeController
     */
    private MyContactTypeController $myContactTypeController;

    /**
     * @var GoalsPaymentsController $goalsPaymentsController
     */
    private GoalsPaymentsController $goalsPaymentsController;

    /**
     * @var MyIssueContactController $myIssueContactController
     */
    private MyIssueContactController $myIssueContactController;

    /**
     * @var MyIssueProgressController $myIssueProgressController
     */
    private MyIssueProgressController $myIssueProgressController;

    /**
     * @var MyPasswordsGroupsController $myPasswordsGroupsController
     */
    private MyPasswordsGroupsController $myPasswordsGroupsController;

    /**
     * @var MyPaymentsIncomeController $myPaymentsIncomeController
     */
    private MyPaymentsIncomeController $myPaymentsIncomeController;

    /**
     * @var MyRecurringPaymentsMonthlyController $myRecurringPaymentsMonthlyController
     */
    private MyRecurringPaymentsMonthlyController $myRecurringPaymentsMonthlyController;

    /**
     * @var MyPaymentsProductsController $myPaymentsProductsController
     */
    private MyPaymentsProductsController $myPaymentsProductsController;

    /**
     * @var MyPaymentsOwedController $myPaymentsOwedController
     */
    private MyPaymentsOwedController $myPaymentsOwedController;

    /**
     * @var MyPaymentsMonthlyController $myPaymentsMonthlyController
     */
    private MyPaymentsMonthlyController $myPaymentsMonthlyController;

    /**
     * @var MyPaymentsBillsItemsController $myPaymentsBillsItemsController
     */
    private MyPaymentsBillsItemsController $myPaymentsBillsItemsController;

    /**
     * @var ModuleDataController $moduleDataController
     */
    private ModuleDataController $moduleDataController;

    /**
     * @var MyScheduleRemindersController $myScheduleReminderController
     */
    private MyScheduleRemindersController $myScheduleReminderController;

    /**
     * @var FileUploadController $fileUploadController
     */
    private FileUploadController $fileUploadController;

    /**
     * @return MyIssuesController
     */
    public function getMyIssuesController(): MyIssuesController {
        return $this->myIssuesController;
    }

    /**
     * @return MyImagesController
     */
    public function getMyImagesController(): MyImagesController {
        return $this->myImagesController;
    }

    /**
     * @return MyContactsController
     */
    public function getMyContactController(): MyContactsController {
        return $this->myContactController;
    }

    /**
     * @return MyContactsSettingsController
     */
    public function getMyContactSettingsController(): MyContactsSettingsController {
        return $this->myContactSettingsController;
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
     * @return ReportsController
     */
    public function getReportsControllers(): ReportsController {
        return $this->reportsControllers;
    }

    /**
     * @return LockedResourceController
     */
    public function getLockedResourceController(): LockedResourceController {
        return $this->lockedResourceController;
    }

    /**
     * @return MyFilesController
     */
    public function getMyFilesController(): MyFilesController {
        return $this->myFilesController;
    }

    /**
     * @return FilesTagsController
     */
    public function getFilesTagsController(): FilesTagsController {
        return $this->filesTagsController;
    }

    /**
     * @return MyJobSettingsController
     */
    public function getMyJobSettingsController(): MyJobSettingsController {
        return $this->myJobSettingsController;
    }

    /**
     * @return MyPasswordsController
     */
    public function getMyPasswordsController(): MyPasswordsController {
        return $this->myPasswordsController;
    }

    /**
     * @return MySchedulesController
     */
    public function getMySchedulesController(): MySchedulesController {
        return $this->mySchedulesController;
    }

    /**
     * @return MyTravelsIdeasController
     */
    public function getMyTravelsIdeasController(): MyTravelsIdeasController {
        return $this->myTravelsIdeasController;
    }

    /**
     * @return MyPaymentsBillsController
     */
    public function getMyPaymentsBillsController(): MyPaymentsBillsController {
        return $this->myPaymentsBillsController;
    }

    /**
     * @return MyPaymentsSettingsController
     */
    public function getMyPaymentsSettingsController(): MyPaymentsSettingsController {
        return $this->myPaymentsSettingsController;
    }

    /**
     * @return SecurityController
     */
    public function getSecurityController(): SecurityController {
        return $this->securityController;
    }

    /**
     * @return SettingsController
     */
    public function getSettingsController(): SettingsController {
        return $this->settingsController;
    }

    /**
     * @return SettingsDashboardController
     */
    public function getSettingsDashboardController(): SettingsDashboardController {
        return $this->settingsDashboardController;
    }

    /**
     * @return SettingsFinancesController
     */
    public function getSettingsFinancesController(): SettingsFinancesController {
        return $this->settingsFinancesController;
    }

    /**
     * @return SettingsValidationController
     */
    public function getSettingsValidationController(): SettingsValidationController {
        return $this->settingsValidationController;
    }

    /**
     * @return SettingsViewController
     */
    public function getSettingsViewController(): SettingsViewController {
        return $this->settingsViewController;
    }

    /**
     * @return FilesUploadSettingsController
     */
    public function getFilesUploadSettingsController(): FilesUploadSettingsController {
        return $this->filesUploadSettingsController;
    }

    /**
     * @return MyJobHolidaysPoolController
     */
    public function getMyJobHolidaysPoolController(): MyJobHolidaysPoolController {
        return $this->myJobHolidaysPoolController;
    }

    /**
     * @return MyTodoController
     */
    public function getMyTodoController(): MyTodoController {
        return $this->myTodoController;
    }

    /**
     * @return MyTodoElementController
     */
    public function getMyTodoElementController(): MyTodoElementController {
        return $this->myTodoElementController;
    }

    /**
     * @return ModuleController
     */
    public function getModuleController(): ModuleController {
        return $this->moduleController;
    }

    /**
     * @return GoalsListController
     */
    public function getGoalsListController(): GoalsListController {
        return $this->goalsListController;
    }

    /**
     * @return UserController
     */
    public function getUserController(): UserController
    {
        return $this->userController;
    }

    /**
     * @return MyVideoController
     */
    public function getMyVideoController(): MyVideoController
    {
        return $this->myVideoController;
    }

    /**
     * @return MyContactTypeController
     */
    public function getMyContactTypeController(): MyContactTypeController
    {
        return $this->myContactTypeController;
    }

    /**
     * @return GoalsPaymentsController
     */
    public function getGoalsPaymentsController(): GoalsPaymentsController
    {
        return $this->goalsPaymentsController;
    }

    /**
     * @return MyIssueContactController
     */
    public function getMyIssueContactController(): MyIssueContactController
    {
        return $this->myIssueContactController;
    }

    /**
     * @return MyIssueProgressController
     */
    public function getMyIssueProgressController(): MyIssueProgressController
    {
        return $this->myIssueProgressController;
    }

    /**
     * @return MyPasswordsGroupsController
     */
    public function getMyPasswordsGroupsController(): MyPasswordsGroupsController
    {
        return $this->myPasswordsGroupsController;
    }

    /**
     * @return MyPaymentsIncomeController
     */
    public function getMyPaymentsIncomeController(): MyPaymentsIncomeController
    {
        return $this->myPaymentsIncomeController;
    }

    /**
     * @return MyRecurringPaymentsMonthlyController
     */
    public function getMyRecurringPaymentsMonthlyController(): MyRecurringPaymentsMonthlyController
    {
        return $this->myRecurringPaymentsMonthlyController;
    }

    /**
     * @return MyPaymentsProductsController
     */
    public function getMyPaymentsProductsController(): MyPaymentsProductsController
    {
        return $this->myPaymentsProductsController;
    }

    /**
     * @return MyPaymentsOwedController
     */
    public function getMyPaymentsOwedController(): MyPaymentsOwedController
    {
        return $this->myPaymentsOwedController;
    }

    /**
     * @return MyPaymentsMonthlyController
     */
    public function getMyPaymentsMonthlyController(): MyPaymentsMonthlyController
    {
        return $this->myPaymentsMonthlyController;
    }

    /**
     * @return MyPaymentsBillsItemsController
     */
    public function getMyPaymentsBillsItemsController(): MyPaymentsBillsItemsController
    {
        return $this->myPaymentsBillsItemsController;
    }

    /**
     * @return ModuleDataController
     */
    public function getModuleDataController(): ModuleDataController
    {
        return $this->moduleDataController;
    }

    /**
     * @return MyScheduleRemindersController
     */
    public function getMyScheduleReminderController(): MyScheduleRemindersController
    {
        return $this->myScheduleReminderController;
    }

    /**
     * @return FileUploadController
     */
    public function getFileUploadController(): FileUploadController
    {
        return $this->fileUploadController;
    }

    /**
     * @param FileUploadController $fileUploadController
     */
    public function setFileUploadController(FileUploadController $fileUploadController): void
    {
        $this->fileUploadController = $fileUploadController;
    }

    public function __construct(
        ReportsController             $reportsController,
        MyNotesController             $myNotesController,
        MyNotesCategoriesController   $myNotesCategoriesController,
        LockedResourceController      $lockedResourceController,
        MyContactsSettingsController  $myContactSettingsController,
        MyContactsController          $myContactController,
        MyImagesController            $myImagesController,
        MyFilesController             $myFilesController,
        FilesTagsController           $filesTagsController,
        MyJobSettingsController       $myJobSettingsController,
        MyPasswordsController         $myPasswordsController,
        MySchedulesController         $mySchedulesController,
        MyTravelsIdeasController      $myTravelsIdeasController,
        MyPaymentsBillsController     $myPaymentsBillsController,
        MyPaymentsSettingsController  $myPaymentsSettingsController,
        SecurityController            $securityController,
        SettingsController            $settingsController,
        SettingsDashboardController   $settingsDashboardController,
        SettingsFinancesController    $settingsFinancesController,
        SettingsValidationController  $settingsValidationController,
        SettingsViewController        $settingsViewController,
        FilesUploadSettingsController $filesUploadSettingsController,
        MyIssuesController            $myIssuesController,
        MyJobHolidaysPoolController   $myJobHolidaysPoolController,
        MyTodoController              $myTodoController,
        MyTodoElementController       $myTodoElementController,
        ModuleController              $moduleController,
        GoalsListController           $goalsListController,
        UserController                $userController,
        MyVideoController             $myVideoController,
        MyContactTypeController       $myContactTypeController,
        GoalsPaymentsController       $goalsPaymentsController,
        MyIssueContactController      $myIssueContactController,
        MyIssueProgressController     $myIssueProgressController,
        MyPasswordsGroupsController   $myPasswordsGroupsController,
        MyPaymentsIncomeController    $myPaymentsIncomeController,
        MyPaymentsProductsController  $myPaymentsProductsController,
        MyPaymentsOwedController      $myPaymentsOwedController,
        MyPaymentsMonthlyController   $myPaymentsMonthlyController,
        ModuleDataController          $moduleDataController,
        MyRecurringPaymentsMonthlyController $myRecurringPaymentsMonthlyController,
        MyPaymentsBillsItemsController       $myPaymentsBillsItemsController,
        MyScheduleRemindersController        $myScheduleReminderController,
        FileUploadController                 $fileUploadController
    ) {
        $this->reportsControllers           = $reportsController;
        $this->myNotesController            = $myNotesController;
        $this->myNotesCategoriesController  = $myNotesCategoriesController;
        $this->lockedResourceController     = $lockedResourceController;
        $this->myContactSettingsController  = $myContactSettingsController;
        $this->myContactController          = $myContactController;
        $this->myImagesController           = $myImagesController;
        $this->myFilesController            = $myFilesController;
        $this->filesTagsController          = $filesTagsController;
        $this->myJobSettingsController      = $myJobSettingsController;
        $this->myPasswordsController        = $myPasswordsController;
        $this->mySchedulesController        = $mySchedulesController;
        $this->myTravelsIdeasController     = $myTravelsIdeasController;
        $this->myPaymentsBillsController    = $myPaymentsBillsController;
        $this->myPaymentsSettingsController = $myPaymentsSettingsController;
        $this->securityController           = $securityController;
        $this->settingsController           = $settingsController;
        $this->settingsFinancesController   = $settingsFinancesController;
        $this->settingsDashboardController  = $settingsDashboardController;
        $this->settingsValidationController = $settingsValidationController;
        $this->settingsViewController       = $settingsViewController;
        $this->filesUploadSettingsController= $filesUploadSettingsController;
        $this->myIssuesController           = $myIssuesController;
        $this->myJobHolidaysPoolController  = $myJobHolidaysPoolController;
        $this->myTodoController             = $myTodoController;
        $this->myTodoElementController      = $myTodoElementController;
        $this->moduleController             = $moduleController;
        $this->goalsListController          = $goalsListController;
        $this->userController               = $userController;
        $this->myVideoController            = $myVideoController;
        $this->myContactTypeController      = $myContactTypeController;
        $this->goalsPaymentsController      = $goalsPaymentsController;
        $this->myIssueContactController     = $myIssueContactController;
        $this->myIssueProgressController    = $myIssueProgressController;
        $this->myPasswordsGroupsController  = $myPasswordsGroupsController;
        $this->myPaymentsIncomeController   = $myPaymentsIncomeController;
        $this->myPaymentsProductsController = $myPaymentsProductsController;
        $this->myPaymentsOwedController     = $myPaymentsOwedController;
        $this->myPaymentsMonthlyController  = $myPaymentsMonthlyController;
        $this->moduleDataController         = $moduleDataController;

        $this->myRecurringPaymentsMonthlyController = $myRecurringPaymentsMonthlyController;
        $this->myPaymentsBillsItemsController       = $myPaymentsBillsItemsController;
        $this->myScheduleReminderController         = $myScheduleReminderController;
        $this->fileUploadController                 = $fileUploadController;
    }

}