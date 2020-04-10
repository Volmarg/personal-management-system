<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyRecurringPaymentsMonthlyType extends AbstractType {

    const KEY_DATE          = 'date';
    const KEY_MONEY         = 'money';
    const KEY_DESCRIPTION   = 'description';
    const KEY_TYPE          = 'type';
    const KEY_SUBMIT        = 'submit';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $payments_types = $this->app->repositories->myPaymentsSettingsRepository->findBy(['deleted' => 0, 'name' => 'type']);

        $builder
            ->add(self::KEY_DATE, DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.date')

            ])
            ->add(self::KEY_MONEY, NumberType::class, [
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.money')
            ])
            ->add(self::KEY_DESCRIPTION, null, [
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.description')
            ])
            ->add(self::KEY_TYPE, EntityType::class, [
                'class' => MyPaymentsSettings::class,
                'choices' => $payments_types,
                'choice_label' => function (MyPaymentsSettings $payment_type) {
                    return $payment_type->getValue();
                },
                'attr' => [
                    'required' => true,
                ],
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.type')
            ]);

            $builder->add('save', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.save')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyRecurringPaymentMonthly::class,
        ]);
    }
}
