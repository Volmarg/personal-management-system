<?php

namespace App\Form\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsOwed;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsOwedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target', TextType::class, [
                'attr' => [
                    'placeholder' => 'Who owes the money'
                ]
            ])
            ->add('information', TextType::class, [
                'attr' => [
                    'placeholder' => 'Information about this borrow'
                ]
            ])
            ->add('date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off',
                    'placeholder'               => 'yyyy-mm-dd'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'required'  => false
            ])
            ->add('amount', IntegerType::class, [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => 'Amount of money owed'
                ]
            ])
            ->add('owedByMe', CheckboxType::class, [
                'required' => false
            ])
            ->add('submit', SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyPaymentsOwed::class,
        ]);
    }
}
