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

    private static $originalData;
    private static $modifiedKeys;

    /**
     * @param FormEvent $event
     * @return FormEvent
     * @throws \Exception
     */
    public static function postSubmit(FormEvent $event) {
        $modifiedData = $event->getForm()->getData();

        $modifiedData = Utils::modifyEventData(static::$originalData, $modifiedData);
        $event->setData($modifiedData);

        return $event;
    }

    /**
     * @param FormEvent $event
     * @param array $modifiedKeys
     * @param array $choices
     * @return FormEvent
     * @throws \Exception
     */
    public static function preSubmit(FormEvent $event, array $modifiedKeys, array $choices) {
        static::$originalData = $modifiedData = $event->getData();
        static::$modifiedKeys = $modifiedKeys;

        # This step is important because we need array where keys are the same as values, and are strings
        if(!empty($choices)){
            $choices = array_combine(
                array_values($choices),
                array_values($choices)
            );
        }

        foreach ($modifiedKeys as $key) {
            $dataElementsToModify[$key] = array_keys($choices)[0];
        }

        $modifiedData = Utils::modifyEventData($dataElementsToModify, $modifiedData);
        $event->setData($modifiedData);

        return $event;
    }

}
