<?php

namespace App\Form\System;

use App\Controller\AppController;
use App\Controller\Utils\Application;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemLockResourcesPasswordType extends AbstractType
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(AppController::KEY_SYSTEM_LOCK_PASSWORD, PasswordType::class, [
                'label' => $this->app->translator->translate('forms.systemLockPassword.labels.password'),
                'attr'  => [
                    "placeholder" => $this->app->translator->translate("forms.systemLockPassword.placeholders.password")
                ]
            ])
            ->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
