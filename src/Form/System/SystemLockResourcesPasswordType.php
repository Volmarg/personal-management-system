<?php

namespace App\Form\System;

use App\Action\System\AppAction;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemLockResourcesPasswordType extends AbstractType
{

    const RESOLVER_OPTION_IS_CREATE_PASSWORD = "isCreatePassword";

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
     * 
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $isCreatePassword = $options[self::RESOLVER_OPTION_IS_CREATE_PASSWORD];

        switch( $isCreatePassword ){
            case true:
                {
                    $builder->add(AppAction::KEY_SYSTEM_LOCK_PASSWORD, PasswordType::class,[
                        'label' => $this->app->translator->translate('forms.systemLockPassword.labels.password'),
                        'attr'  => [
                            "placeholder" => $this->app->translator->translate("forms.systemLockPassword.placeholders.password"),
                            "data-id"     => 'systemLockPassword',
                        ]
                    ]);
                }
                break;
            default:
                {
                    $placeholderLabel = ( Env::isDemo() ? User::DEMO_PASSWORD : $this->app->translator->translate("forms.systemLockPassword.placeholders.password") );

                    $builder->add(AppAction::KEY_SYSTEM_LOCK_PASSWORD, PasswordType::class, [
                        'label' => $this->app->translator->translate('forms.systemLockPassword.labels.password'),
                        'attr'  => [
                            "placeholder" => $placeholderLabel,
                        ]
                    ]);
                }
        }

        $builder
            ->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);

        $resolver->setRequired(self::RESOLVER_OPTION_IS_CREATE_PASSWORD);
    }
}
