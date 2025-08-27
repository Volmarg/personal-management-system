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
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Repository\SettingRepository;
use App\Repository\System\LockedResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var MyPaymentsMonthlyRepository
     */
    public $myPaymentsMonthlyRepository;

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
     * @var MyRecurringPaymentMonthlyRepository
     */
    public $myRecurringPaymentMonthlyRepository;

    /**
     * @var SettingRepository
     */
    public $settingRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    /**
     * @var MyTodoRepository $myTodoRepository
     */
    public $myTodoRepository;

    public function __construct(
        MyPaymentsMonthlyRepository         $myPaymentsMonthlyRepository,
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository         $myNotesCategoriesRepository,
        MyPasswordsRepository               $myPasswordsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        MyGoalsPaymentsRepository           $myGoalsPaymentsRepository,
        FilesTagsRepository                 $filesTagsRepository,
        MyRecurringPaymentMonthlyRepository $myRecurringMonthlyPaymentRepository,
        SettingRepository                   $settingRepository,
        LockedResourceRepository            $lockedResourceRepository,
        MyTodoRepository                    $myTodoRepository,
    ) {
        $this->myPaymentsMonthlyRepository          = $myPaymentsMonthlyRepository;
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository          = $myNotesCategoriesRepository;
        $this->myPasswordsRepository                = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->myGoalsPaymentsRepository            = $myGoalsPaymentsRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->myRecurringPaymentMonthlyRepository  = $myRecurringMonthlyPaymentRepository;
        $this->settingRepository                    = $settingRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->myTodoRepository                     = $myTodoRepository;
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
