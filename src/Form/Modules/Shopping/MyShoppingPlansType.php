<?php

namespace App\Form\Modules\Shopping;

use App\Entity\Modules\Shopping\MyShoppingPlans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyShoppingPlansType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('Name')
            ->add('Information')
            ->add('Example')
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyShoppingPlans::class,
        ]);
    }
}
