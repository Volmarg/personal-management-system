<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\Entity\Modules\Payments\MyPaymentsOwed;
use App\Form\Type\RoundcheckboxType;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsOwedType extends AbstractType
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
     *
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $financesCurrenciesDtos = $this->app->settings->settingsLoader->getCurrenciesDtosForSettingsFinances();
        $choices                = $this->buildCurrencyChoices($financesCurrenciesDtos);

        $builder
            ->add('target', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.target')
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.target')
            ])
            ->add('information', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.information')
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.information')
            ])
            ->add('date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off',
                    'placeholder'               => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.date')
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'required'  => false,
                'label'     => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.date'),
                'html5'     => false,
            ])
            ->add('amount', NumberType::class, [
                'attr' => [
                    'min'           => 0.1,
                    "step"          => 0.01,
                    'placeholder'   => $this->app->translator->translate('forms.MyPaymentsOwedType.placeholders.amount')
                ],
                'label'     => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.amount'),
                "html5"     => true,
            ])
            ->add('currency', ChoiceType::class, [
                'label'        => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.currency'),
                'choices'      => $choices,
                "required"     => true,
                "data"         => false,    // this skips some internal validation for choices and allows to save strings, not just int
                'attr'         => [
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
                ],
            ])
            ->add('owedByMe', RoundcheckboxType::class, [
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyPaymentsOwedType.labels.owedByMe')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyPaymentsOwed::class,
        ]);
    }

    /**
     * @param SettingsCurrencyDTO[] $settingsCurrenciesDtos
     * @return array
     */
    private function buildCurrencyChoices(array $settingsCurrenciesDtos){
        $choices = [];

        foreach($settingsCurrenciesDtos as $settingCurrencyDto ){
            $value           = $settingCurrencyDto->getName();
            $name            = $settingCurrencyDto->getSymbol();
            $choices[$name]  = $value;
        }

        return $choices;
    }

}
