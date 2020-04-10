<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsSettingsCurrencyMultiplierType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', HiddenType::class, [
                'data'  => 'currency_multiplier',
                'label' => $this->app->translator->translate('forms.MyPaymentsSettingsCurrencyMultiplierType.labels.name')
            ])
            ->add('value', NumberType::class, [
                'label' => $this->app->translator->translate('forms.MyPaymentsSettingsCurrencyMultiplierType.labels.value'),
                'data'  => $this->app->repositories->myPaymentsSettingsRepository->fetchCurrencyMultiplier()
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.save')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsSettings::class,
        ]);
    }

}
