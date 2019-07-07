<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 23.05.19
 * Time: 06:14
 */

namespace App\Form\Events;

/**
 * Class DatalistLogicOverride
 * @package App\Form\Events
 *
 * This class is used to override DataListType behaviour which is based on ChoiceType
 * This logic allows user to either pick up anything from list or enter his own value
 * Without it if You enter some Value (even if it's on list) the form isValid() = false;
 */

use Symfony\Component\Form\FormEvent;

class DatalistLogicOverride {

    private static $original_data;
    private static $modified_keys;

    /**
     * @param FormEvent $event
     * @return FormEvent
     * @throws \Exception
     */
    public static function postSubmit(FormEvent $event) {
        $modified_data = $event->getForm()->getData();

        $modified_data = Utils::modifyEventData(static::$original_data, $modified_data);
        $event->setData($modified_data);

        return $event;
    }

    /**
     * @param FormEvent $event
     * @param array $modified_keys
     * @param array $choices
     * @return FormEvent
     * @throws \Exception
     */
    public static function preSubmit(FormEvent $event, array $modified_keys, array $choices) {
        static::$original_data = $modified_data = $event->getData();
        static::$modified_keys = $modified_keys;

        # This step is important because we need array where keys are the same as values, and are strings
        if(!empty($choices)){
            $choices = array_combine(
                array_values($choices),
                array_values($choices)
            );
        }

        foreach ($modified_keys as $key) {
            $data_elements_to_modify[$key] = array_keys($choices)[0];
        }

        $modified_data = Utils::modifyEventData($data_elements_to_modify, $modified_data);
        $event->setData($modified_data);

        return $event;
    }

}
