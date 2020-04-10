<?php

namespace App\Form\Page\Settings\Finances;

use App\Controller\Core\Application;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\Form\Type\RoundcheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(SettingsCurrencyDTO::KEY_NAME, TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.CurrencyType.placeholders.name')
                ],
                'label' => $this->app->translator->translate('forms.CurrencyType.labels.name')
            ])
            ->add(SettingsCurrencyDTO::KEY_SYMBOL, TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.CurrencyType.placeholders.symbol')
                ],
                'label' => $this->app->translator->translate('forms.CurrencyType.labels.symbol')
            ])
            ->add(SettingsCurrencyDTO::KEY_MULTIPLIER, NumberType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.CurrencyType.placeholders.multiplier'),
                    "min"         => 0.01,
                    "step"        => 0.01,
                ],
                'label' => $this->app->translator->translate('forms.CurrencyType.labels.multiplier')
            ])
            ->add(SettingsCurrencyDTO::KEY_IS_DEFAULT, RoundcheckboxType::class, [
                'required' => false,
                'label' => $this->app->translator->translate('forms.CurrencyType.labels.isDefault')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
