<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:05
 */

namespace App\Controller\Utils;


use App\Form\MyNotesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class Forms extends AbstractController {

    /**
     * @var FormInterface $myNotesForm
     */
    public $myNotesForm;

    public function __construct() {
        //$this->myNotesForm = $this->createForm(MyNotesType::class);
    }

}