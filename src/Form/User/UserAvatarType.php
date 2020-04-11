<?php

namespace App\Form\User;

use App\Entity\User;
use App\Services\Core\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAvatarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('avatar',null,[
                'attr'=>[
                    'data-id' => "avatar",
                    'placeholder' => $translator->translate('forms.UserAvatarType.placeholders.avatar')
                ],
                'data' => $options['avatar'],
                'label'   => $translator->translate('forms.UserAvatarType.labels.avatar')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $translator->translate('forms.general.submit')
            ])
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
