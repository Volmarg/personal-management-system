<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FontawesomepickerType extends AbstractType {

    public function getParent() {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
    }

}