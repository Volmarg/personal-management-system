<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\DTO\Settings\Finances\SettingsCurrencyDTO;
use App\Entity\Modules\Payments\MyPaymentsIncome;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsIncomeType extends AbstractType
{

    const KEY_INFORMATION = "information";
    const KEY_DATE        = "date";
    const KEY_AMOUNT      = "amount";
    const KEY_CURRENCY    = "currency";

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
            ->add(self::KEY_INFORMATION, TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyPaymentsIncomeType.placeholders.information')
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsIncomeType.labels.information')
            ])
            ->add(self::KEY_DATE, DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off',
                    'placeholder'               => $this->app->translator->translate('forms.MyPaymentsIncomeType.placeholders.date')
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'required'  => false,
                'label'     => $this->app->translator->translate('forms.MyPaymentsIncomeType.labels.date'),
                'html5'     => false,
            ])
            ->add(self::KEY_AMOUNT, NumberType::class, [
                'attr' => [
                    'min'           => 0.1,
                    "step"          => 0.01,
                    'placeholder'   => $this->app->translator->translate('forms.MyPaymentsIncomeType.placeholders.amount')
                ],
                'label'     => $this->app->translator->translate('forms.MyPaymentsIncomeType.labels.amount'),
                "html5"     => true,
            ])
            ->add(self::KEY_CURRENCY, ChoiceType::class, [
                'label'        => $this->app->translator->translate('forms.MyPaymentsIncomeType.labels.currency'),
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
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyPaymentsIncome::class,
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
