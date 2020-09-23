<?php

namespace App\Form\Events\Modules;

use App\Controller\Core\Controllers;
use Symfony\Component\Form\FormEvent;

//todo: must be expanded in case of having issue already existing but it's marked as active/deleted
// given relation should receive new issue and the old
// would be nice to have some global history table with json data inside HistoryDto - but that;s for far later

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