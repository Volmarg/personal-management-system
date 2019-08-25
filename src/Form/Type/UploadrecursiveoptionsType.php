<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * This type has been specifically created for upload based modules, it works with directories tree array
 * just like it is done with recursive folders menu also for uploadType
 *
 * This name of class MUST be written like this (at least in Symfony 4.2) otherwise symfony won't load twig template for this type
 *
 * Class DatalistType
 * @package App\Form\Type
 */
class UploadrecursiveoptionsType extends AbstractType {

    public function getParent() {
        return ChoiceType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
        $view->vars['choices'] = $options['choices'];
    }

}