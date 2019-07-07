<?php

namespace App\Form\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsProductsType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('Name')
            ->add('Price')
            ->add('Market')
            ->add('Products')
            ->add('Information')
            ->add('Rejected')
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsProduct::class,
        ]);
    }
}
