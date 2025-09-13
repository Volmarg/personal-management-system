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
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\System\LockedResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var MyPaymentsSettingsRepository
     */
    public $myPaymentsSettingsRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    public function __construct(
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        FilesTagsRepository                 $filesTagsRepository,
        LockedResourceRepository            $lockedResourceRepository,
    ) {
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
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
