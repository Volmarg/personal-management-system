<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Core;


use App\Entity\Interfaces\EntityInterface;
use App\Repository\System\LockedResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Repositories extends AbstractController {

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    public function __construct(
        LockedResourceRepository            $lockedResourceRepository,
    ) {
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
