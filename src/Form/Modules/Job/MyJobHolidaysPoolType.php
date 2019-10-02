<?php

namespace App\Form\Modules\Job;

use App\Entity\Modules\Job\MyJobHolidaysPool;
use App\Services\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyJobHolidaysPoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('year', IntegerType::class, [
                'attr' => [
                    'placeholder' => $translator->translate('forms.MyJobHolidaysPoolType.placeholders.year')
                ]
            ])
            ->add('DaysLeft', IntegerType::class, [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => $translator->translate('forms.MyJobHolidaysPoolType.placeholders.daysLeft')
                ]
            ])
            ->add('CompanyName', TextType::class, [
                'attr' => [
                    'placeholder' => $translator->translate('forms.MyJobHolidaysPoolType.placeholders.companyName')
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
