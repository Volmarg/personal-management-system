<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAvatarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar',null,[
                'attr'=>[
                    'data-id' => "avatar",
                    'placeholder' => 'Internal or external url to some image'
                ],
                'data' => $options['avatar'],
                'label'   => 'Avatar url'
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

        $resolver->setRequired('avatar');
    }

    public function getBlockPrefix()
    {
        return 'user';
    }
}
