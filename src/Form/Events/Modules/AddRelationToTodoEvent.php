<?php

namespace App\Form\Events\Modules;

use App\Controller\Core\Controllers;
use Symfony\Component\Form\FormEvent;

/**
 * Will handle adding relation with `todo` module with given module
 *
 * Class AddRelationToTodoEvent
 * @package App\Form\Events\Modules
 */
class AddRelationToTodoEvent {

    /**
     * Will add the relation to given module upon submitting form
     *
     * @param FormEvent $event
     * @param Controllers $controllers
     */
    public static function postEvent(FormEvent $event, Controllers $controllers)
    {
        $todo = $event->getData();
        $controllers->getMyTodoController()->setRelationForTodo($todo);
    }

}