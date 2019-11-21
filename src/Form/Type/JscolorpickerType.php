<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JscolorpickerType extends AbstractType {

    public function getParent() {
        return ColorType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
    }

}