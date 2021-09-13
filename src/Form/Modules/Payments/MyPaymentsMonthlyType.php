<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsMonthlyType extends AbstractType {

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
            ->add('date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $this->app->translator->translate('forms.MyPaymentsMonthlyType.labels.date'),
                "html5"     => false,
            ])
            ->add('money', NumberType::class, [
                'label' => $this->app->translator->translate('forms.MyPaymentsMonthlyType.labels.money')
            ])
            ->add('description', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsMonthlyType.labels.description')
            ])
            ->add('type', EntityType::class, [
                'class' => MyPaymentsSettings::class,
                'choices' => $paymentsTypes,
                'choice_label' => function (MyPaymentsSettings $paymentType) {
                    return $paymentType->getValue();
                },
                'attr' => [
                    'required'                                       => true,
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsMonthlyType.labels.type')
            ]);

            $builder->add('save', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.save')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsMonthly::class,
        ]);
    }
}
