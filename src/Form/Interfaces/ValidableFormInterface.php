<?php


namespace App\Form\Interfaces;

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