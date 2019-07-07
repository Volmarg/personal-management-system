<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserNicknameType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('nickname', null, [
                'attr' => [
                    'data-id' => 'nickname',
                    'placeholder' => 'This will be displayed on page but login will not change.'
                ],
                'data' => $options['nickname'],
            ])
            ->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
        $resolver->setRequired('nickname');
    }
}
