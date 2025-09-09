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
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Todo\MyTodoRepository;
use App\Repository\System\LockedResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var MyPaymentsSettingsRepository
     */
    public $myPaymentsSettingsRepository;

    /**
     * @var MyPasswordsGroupsRepository
     */
    public $myPasswordsGroupsRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    /**
     * @var MyTodoRepository $myTodoRepository
     */
    public $myTodoRepository;

    public function __construct(
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        FilesTagsRepository                 $filesTagsRepository,
        LockedResourceRepository            $lockedResourceRepository,
        MyTodoRepository                    $myTodoRepository,
    ) {
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
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
