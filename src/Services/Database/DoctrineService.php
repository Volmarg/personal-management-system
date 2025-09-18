<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Services\Database;

use App\Entity\Interfaces\EntityInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DoctrineService extends AbstractController
{

    /**
     * @param $object
     *
     * @return bool
     */
    public static function isEntity($object): bool
    {
        if (!is_object($object) || !($object instanceof EntityInterface)) {
            return false;
        }

        return true;
    }

}
