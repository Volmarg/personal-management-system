<?php

namespace App\Form\Type;

use App\Form\DataTransformers\StringToDateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class DatetimepickerType extends AbstractType {

    public function getParent() {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addModelTransformer(new StringToDateTimeTransformer());
    }

}