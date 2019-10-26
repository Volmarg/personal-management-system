<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoundcheckboxType extends AbstractType {

    public function getParent() {
        return CheckboxType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
        $view->vars['label'] = $options['label'];
    }

}