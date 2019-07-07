<?php

namespace App\Form\Modules\Achievements;

use App\Entity\Modules\Achievements\Achievement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('Name')
            ->add('Description')
            ->add('Type', ChoiceType::class, [
                'choices' => $options['enum_types']
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Achievement::class,
        ]);
        $resolver->setRequired('enum_types');
    }
}
