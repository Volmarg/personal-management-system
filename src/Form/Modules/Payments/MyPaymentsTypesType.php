<?php

namespace App\Form\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsTypesType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', HiddenType::class, [
                'data' => 'type'
            ])
            ->add('value', null, [
                'label' => 'Type name'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsSettings::class,
        ]);
    }
}
