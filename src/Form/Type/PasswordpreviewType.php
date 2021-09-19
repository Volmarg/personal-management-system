<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PasswordpreviewType extends AbstractType {

    public function getParent() {
        return PasswordType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
    }

}