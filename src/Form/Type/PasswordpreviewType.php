<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordpreviewType extends AbstractType {

    const OPTION_INCLUDE_GENERATE_PASSWORD = "includeGeneratePassword";

    public function getParent() {
        return PasswordType::class;
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars[self::OPTION_INCLUDE_GENERATE_PASSWORD] = $options[self::OPTION_INCLUDE_GENERATE_PASSWORD];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault(self::OPTION_INCLUDE_GENERATE_PASSWORD, false);
    }

}