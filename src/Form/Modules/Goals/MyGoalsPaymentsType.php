<?php

namespace App\Form\Modules\Goals;

use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Services\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyGoalsPaymentsType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('name')
            ->add('collectionStartDate', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $translator->translate('forms.MyGoalsPaymentsType.collectionStartDate')
            ])
            ->add('deadline', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $translator->translate('forms.MyGoalsPaymentsType.deadline')
            ])
            ->add('moneyGoal', IntegerType::class,[
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('moneyCollected', IntegerType::class, [
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('displayOnDashboard',CheckboxType::class,[
                'label'     => $translator->translate('forms.MyGoalsPaymentsType.displayOnDashboard'),
                'required'  => false
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyGoalsPayments::class,
        ]);
    }
}
