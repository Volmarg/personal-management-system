<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsBills as MyPaymentsBillsEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsBills extends AbstractType {

    const START_DATE        = 'startDate';
    const END_DATE          = 'endDate';
    const NAME              = 'name';
    const INFORMATION       = 'information';
    const SUBMIT            = 'submit';
    const PLANNED_AMOUNT    = 'plannedAmount';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(static::START_DATE, DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-MM-dd',
                'label'     => $this->app->translator->translate('forms.MyPaymentsBills.labels.startDate'),
                'html5'     => false,
            ])
            ->add(static::END_DATE, DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-MM-dd',
                'label'     => $this->app->translator->translate('forms.MyPaymentsBills.labels.endDate'),
                'html5'     => false,
            ])
            ->add(static::NAME, null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsBills.labels.name')
            ])
            ->add(static::INFORMATION, null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsBills.labels.information')
            ])
            ->add(static::PLANNED_AMOUNT, NumberType::class, [
                "attr"  => [
                    "min" => 1
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsBills.labels.plannedAmount'),
                "html5" => true,
            ]);

            $builder->add(static::SUBMIT, SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsBillsEntity::class,
        ]);
    }
}
