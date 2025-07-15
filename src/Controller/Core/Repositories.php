<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Core;


use App\Entity\Interfaces\EntityInterface;
use App\Repository\FilesTagsRepository;
use App\Repository\Modules\Contacts\MyContactGroupRepository;
use App\Repository\Modules\Contacts\MyContactRepository;
use App\Repository\Modules\Contacts\MyContactTypeRepository;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Issues\MyIssueContactRepository;
use App\Repository\Modules\Issues\MyIssueProgressRepository;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\Repository\Modules\ModuleDataRepository;
use App\Repository\Modules\Notes\MyNotesRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsItemsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsRepository;
use App\Repository\Modules\Payments\MyPaymentsIncomeRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsOwedRepository;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Repository\Modules\Schedules\MyScheduleRepository;
use App\Repository\Modules\Shopping\MyShoppingPlansRepository;
use App\Repository\Modules\Todo\MyTodoElementRepository;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
use App\Repository\SettingRepository;
use App\Repository\System\LockedResourceRepository;
use App\Repository\System\ModuleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var MyNotesRepository $myNotesRepository
     */
    public $myNotesRepository;

    /**
     * @var MyPaymentsMonthlyRepository
     */
    public $myPaymentsMonthlyRepository;

    /**
     * @var MyPaymentsProductRepository
     */
    public $myPaymentsProductRepository;

    /**
     * @var MyShoppingPlansRepository
     */
    public $myShoppingPlansRepository;

    /**
     * @var MyTravelsIdeasRepository
     */
    public $myTravelsIdeasRepository;

    /**
     * @var MyPaymentsSettingsRepository
     */
    public $myPaymentsSettingsRepository;

    /**
     * @var MyNotesCategoriesRepository
     */
    public $myNotesCategoriesRepository;

    /**
     * @var MyPasswordsRepository
     */
    public $myPasswordsRepository;

    /**
     * @var MyPasswordsGroupsRepository
     */
    public $myPasswordsGroupsRepository;

    /**
     * @var UserRepository
     */
    public $userRepository;

    /**
     * @var MyGoalsPaymentsRepository
     */
    public $myGoalsPaymentsRepository;

    /**
     * @var MyJobHolidaysRepository
     */
    public $myJobHolidaysRepository;

    /**
     * @var MyJobHolidaysPoolRepository
     */
    public $myJobHolidaysPoolRepository;

    /**
     * @var MyPaymentsOwedRepository
     */
    public $myPaymentsOwedRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var MyPaymentsBillsRepository $myPaymentsBillsRepository
     */
    public $myPaymentsBillsRepository;

    /**
     * @var MyPaymentsBillsItemsRepository $myPaymentsBillsItemsRepository
     */
    public $myPaymentsBillsItemsRepository;

    /**
     * @var ReportsRepository $reportsRepository
     */
    public $reportsRepository;

    /**
     * @var MyRecurringPaymentMonthlyRepository
     */
    public $myRecurringPaymentMonthlyRepository;

    /**
     * @var SettingRepository
     */
    public $settingRepository;

    /**
     * @var MyScheduleRepository $myScheduleRepository
     */
    public $myScheduleRepository;

    /**
     * @var MyContactRepository $myContactRepository
     */
    public $myContactRepository;

    /**
     * @var MyContactTypeRepository $myContactTypeRepository
     */
    public $myContactTypeRepository;

    /**
     * @var MyContactGroupRepository $myContactGroupRepository
     */
    public $myContactGroupRepository;

    /**
     * @var MyPaymentsIncomeRepository $myPaymentsIncomeRepository
     */
    public $myPaymentsIncomeRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    /**
     * @var MyIssueContactRepository $myIssueContactRepository
     */
    public $myIssueContactRepository;

    /**
     * @var MyIssueProgressRepository $myIssueProgressRepository
     */
    public $myIssueProgressRepository;

    /**
     * @var MyTodoRepository $myTodoRepository
     */
    public $myTodoRepository;

    /**
     * @var MyTodoElementRepository $myTodoElementRepository
     */
    public $myTodoElementRepository;

    /**
     * @var ModuleRepository $moduleRepository
     */
    public $moduleRepository;

    /**
     * @var ModuleDataRepository $moduleDataRepository
     */
    public $moduleDataRepository;

    public function __construct(
        MyNotesRepository                   $myNotesRepository,
        MyPaymentsMonthlyRepository         $myPaymentsMonthlyRepository,
        MyPaymentsProductRepository         $myPaymentsProductRepository,
        MyShoppingPlansRepository           $myShoppingPlansRepository,
        MyTravelsIdeasRepository            $myTravelIdeasRepository,
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository         $myNotesCategoriesRepository,
        MyPasswordsRepository               $myPasswordsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        UserRepository                      $userRepository,
        MyGoalsPaymentsRepository           $myGoalsPaymentsRepository,
        MyJobHolidaysRepository             $myJobHolidaysRepository,
        MyJobHolidaysPoolRepository         $myJobHolidaysPoolRepository,
        MyPaymentsOwedRepository            $myPaymentsOwedRepository,
        FilesTagsRepository                 $filesTagsRepository,
        MyPaymentsBillsRepository           $myPaymentsBillsRepository,
        MyPaymentsBillsItemsRepository      $myPaymentsBillsItemsRepository,
        ReportsRepository                   $reportsRepository,
        MyRecurringPaymentMonthlyRepository $myRecurringMonthlyPaymentRepository,
        SettingRepository                   $settingRepository,
        MyScheduleRepository                $myScheduleRepository,
        MyContactTypeRepository             $myContactTypeRepository,
        MyContactGroupRepository            $myContactGroupRepository,
        MyContactRepository                 $myContactRepository,
        MyPaymentsIncomeRepository          $myPaymentsIncomeRepository,
        LockedResourceRepository            $lockedResourceRepository,
        MyIssueContactRepository            $myIssueContactRepository,
        MyIssueProgressRepository           $myIssueProgressRepository,
        MyTodoRepository                    $myTodoRepository,
        MyTodoElementRepository             $myTodoElementRepository,
        ModuleRepository                    $moduleRepository,
        ModuleDataRepository                $moduleDataRepository,
        EntityManagerInterface              $entityManager,
    ) {
        $this->myNotesRepository                    = $myNotesRepository;
        $this->myPaymentsMonthlyRepository          = $myPaymentsMonthlyRepository;
        $this->myPaymentsProductRepository          = $myPaymentsProductRepository;
        $this->myShoppingPlansRepository            = $myShoppingPlansRepository;
        $this->myTravelsIdeasRepository             = $myTravelIdeasRepository;
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository          = $myNotesCategoriesRepository;
        $this->myPasswordsRepository                = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->userRepository                       = $userRepository;
        $this->myGoalsPaymentsRepository            = $myGoalsPaymentsRepository;
        $this->myJobHolidaysRepository              = $myJobHolidaysRepository;
        $this->myJobHolidaysPoolRepository          = $myJobHolidaysPoolRepository;
        $this->myPaymentsOwedRepository             = $myPaymentsOwedRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->myPaymentsBillsRepository            = $myPaymentsBillsRepository;
        $this->myPaymentsBillsItemsRepository       = $myPaymentsBillsItemsRepository;
        $this->reportsRepository                    = $reportsRepository;
        $this->myRecurringPaymentMonthlyRepository  = $myRecurringMonthlyPaymentRepository;
        $this->settingRepository                    = $settingRepository;
        $this->myScheduleRepository                 = $myScheduleRepository;
        $this->myContactTypeRepository              = $myContactTypeRepository;
        $this->myContactGroupRepository             = $myContactGroupRepository;
        $this->myContactRepository                  = $myContactRepository;
        $this->myPaymentsIncomeRepository           = $myPaymentsIncomeRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->myIssueContactRepository             = $myIssueContactRepository;
        $this->myIssueProgressRepository            = $myIssueProgressRepository;
        $this->entityManager                        = $entityManager;
        $this->myTodoRepository                     = $myTodoRepository;
        $this->myTodoElementRepository              = $myTodoElementRepository;
        $this->moduleRepository                     = $moduleRepository;
        $this->moduleDataRepository                 = $moduleDataRepository;
    }

    /**
     * @param $object
     * @return bool
     */
    public static function isEntity($object): bool
    {
        if(
                !is_object($object)
            ||  !($object instanceof EntityInterface)
        ){
            return false;
        }

        return true;
    }

}
