<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:05
 */

namespace App\Controller\Utils;

use App\Form\Files\MoveSingleFileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

    public function __construct() {

    }

    public function moveSingleFile(array $params = []): FormInterface {
        return $this->createForm(MoveSingleFileType::class, null, $params);
    }

}