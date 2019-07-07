<?php

namespace App\Controller\Messages;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GeneralMessagesController
 * @package App\Controller\Messages
 * In some places i returning common messages from backend to frontend - i store them here
 */

class GeneralMessagesController extends AbstractController
{

    /**
     * MyNotesCategories
     */
    const CATEGORY_EXISTS           = 'Category with this name or id does not exist!';
    const CATEGORY_EMPTY_REDIRECT   = 'The category which You\'ve just tried to enter is empty, therefore You were redirected';

    /**
     * General
     */
    const RECORD_WITH_NAME_EXISTS   = 'Record with this name already exists!';
    const FORM_SUBMITTED            = 'Form was submitted successfully';

}
