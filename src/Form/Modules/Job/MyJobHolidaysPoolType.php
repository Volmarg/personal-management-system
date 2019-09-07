<?php

namespace App\Form\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidaysPool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyJobHolidaysPoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', IntegerType::class, [
                'attr' => [
                    'placeholder' => "Add year"
                ]
            ])
            ->add('DaysLeft', IntegerType::class, [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => 'Amount of days that You have for this year'
                ]
            ])
            ->add('CompanyName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Provide company name'
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyJobHolidaysPool::class,
        ]);
    }
}
