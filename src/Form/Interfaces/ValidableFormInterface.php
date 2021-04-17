<?php


namespace App\Form\Interfaces;

/**
 * This is later used to extract the form prefix, as the prefix is needed on the frontend to mark the errors in the form
 * (from @see \App\Controller\Core\AjaxResponse)
 *
 * Interface ValidableFormInterface
 * @package App\Form\Interfaces
 */
interface ValidableFormInterface
{
    /**
     * @return string
     */
    public static function getFormPrefix(): string;

    /**
     * @return string
     */
    public function getBlockPrefix(): string;
}