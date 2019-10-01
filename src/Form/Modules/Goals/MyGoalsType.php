<?php

namespace App\Form\Modules\Goals;

use App\Entity\Modules\Goals\MyGoals;
use App\Services\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyGoalsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('name')
            ->add('description')
            ->add('displayOnDashboard',CheckboxType::class,[
                'label'     => $translator->translate('forms.MyGoalsType.displayOnDashboard'),
                'required'  => false
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyGoals::class,
        ]);
    }
}
