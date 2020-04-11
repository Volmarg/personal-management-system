<?php

namespace App\Form\User;

use App\Entity\User;
use App\Services\Core\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserNicknameType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('nickname', null, [
                'attr' => [
                    'data-id'     => 'nickname',
                    'placeholder' => $translator->translate('forms.UserNicknameType.placeholder.nickname')
                ],
                'data'  => $options['nickname'],
                'label' => $translator->translate('forms.UserNicknameType.labels.nickname')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $translator->translate('forms.general.submit')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
        $resolver->setRequired('nickname');
    }
}
