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
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsItemsRepository;
use App\Repository\Modules\Payments\MyPaymentsIncomeRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository;
use App\Repository\Modules\Todo\MyTodoElementRepository;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
use App\Repository\SettingRepository;
use App\Repository\System\LockedResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var MyPaymentsMonthlyRepository
     */
    public $myPaymentsMonthlyRepository;

    /**
     * @var MyPaymentsProductRepository
     */
    public $myPaymentsProductRepository;

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
     * @var MyGoalsPaymentsRepository
     */
    public $myGoalsPaymentsRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var MyPaymentsBillsItemsRepository $myPaymentsBillsItemsRepository
     */
    public $myPaymentsBillsItemsRepository;

    /**
     * @var MyRecurringPaymentMonthlyRepository
     */
    public $myRecurringPaymentMonthlyRepository;

    /**
     * @var SettingRepository
     */
    public $settingRepository;

    /**
     * @var MyContactRepository $myContactRepository
     */
    public $myContactRepository;

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
     * @var MyTodoRepository $myTodoRepository
     */
    public $myTodoRepository;

    /**
     * @var MyTodoElementRepository $myTodoElementRepository
     */
    public $myTodoElementRepository;

    public function __construct(
        MyPaymentsMonthlyRepository         $myPaymentsMonthlyRepository,
        MyPaymentsProductRepository         $myPaymentsProductRepository,
        MyTravelsIdeasRepository            $myTravelIdeasRepository,
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository         $myNotesCategoriesRepository,
        MyPasswordsRepository               $myPasswordsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        MyGoalsPaymentsRepository           $myGoalsPaymentsRepository,
        FilesTagsRepository                 $filesTagsRepository,
        MyPaymentsBillsItemsRepository      $myPaymentsBillsItemsRepository,
        MyRecurringPaymentMonthlyRepository $myRecurringMonthlyPaymentRepository,
        SettingRepository                   $settingRepository,
        MyContactGroupRepository            $myContactGroupRepository,
        MyContactRepository                 $myContactRepository,
        MyPaymentsIncomeRepository          $myPaymentsIncomeRepository,
        LockedResourceRepository            $lockedResourceRepository,
        MyTodoRepository                    $myTodoRepository,
        MyTodoElementRepository             $myTodoElementRepository,
    ) {
        $this->myPaymentsMonthlyRepository          = $myPaymentsMonthlyRepository;
        $this->myPaymentsProductRepository          = $myPaymentsProductRepository;
        $this->myTravelsIdeasRepository             = $myTravelIdeasRepository;
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository          = $myNotesCategoriesRepository;
        $this->myPasswordsRepository                = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->myGoalsPaymentsRepository            = $myGoalsPaymentsRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->myPaymentsBillsItemsRepository       = $myPaymentsBillsItemsRepository;
        $this->myRecurringPaymentMonthlyRepository  = $myRecurringMonthlyPaymentRepository;
        $this->settingRepository                    = $settingRepository;
        $this->myContactGroupRepository             = $myContactGroupRepository;
        $this->myContactRepository                  = $myContactRepository;
        $this->myPaymentsIncomeRepository           = $myPaymentsIncomeRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->myTodoRepository                     = $myTodoRepository;
        $this->myTodoElementRepository              = $myTodoElementRepository;
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
