<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Controller\Utils\Utils;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use App\Entity\Modules\Payments\MyRecurringPaymentMonthly;
use App\Form\Interfaces\ValidableFormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyRecurringPaymentsMonthlyType extends AbstractType implements ValidableFormInterface {

    const KEY_DAY_OF_MONTH  = 'dayOfMonth';
    const KEY_MONEY         = 'money';
    const KEY_DESCRIPTION   = 'description';
    const KEY_TYPE          = 'type';
    const KEY_SUBMIT        = 'submit';

    /**
     * @return string
     */
    public static function getFormPrefix(): string {
        return Utils::getClassBasename(MyRecurringPaymentMonthly::class);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string {
        return self::getFormPrefix();
    }

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $paymentsTypes = $this->app->repositories->myPaymentsSettingsRepository->findBy(['deleted' => 0, 'name' => 'type']);

        $builder
            ->add(self::KEY_DAY_OF_MONTH, IntegerType::class, [
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.dayOfMonth'),
                "attr"  => [
                    "min"   => MyRecurringPaymentMonthly::MIN_DAY_OF_MONTH,
                    "max"   => MyRecurringPaymentMonthly::MAX_DAY_OF_MONTH,
                ]
            ])
            ->add(self::KEY_MONEY, NumberType::class, [
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.money'),
            ])
            ->add(self::KEY_DESCRIPTION, null, [
                'label' => $this->app->translator->translate('forms.MyRecurringPaymentMonthlyType.labels.description')
            ])
            ->add(self::KEY_TYPE, EntityType::class, [
                'class' => MyPaymentsSettings::class,
                'choices' => $paymentsTypes,
                'choice_label' => function (MyPaymentsSettings $payment_type) {
                    return $payment_type->getValue();
                },
                'attr' => [
                    'required' => true,
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
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
