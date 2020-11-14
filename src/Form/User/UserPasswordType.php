<?php

namespace App\Form\User;

use App\Entity\User;
use App\Services\Core\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPasswordType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $translator = new Translator();

        $builder
            ->add('password', PasswordType::class, [
                'attr' => [
                    'data-id'     => 'password',
                    "placeholder" => $translator->translate("forms.UserPasswordType.placeholders.password"),

                ],
                'label' => $translator->translate('forms.UserPasswordType.labels.password')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
