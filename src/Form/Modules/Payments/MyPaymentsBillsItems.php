<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsBills as MyPaymentsBillsEntity;
use App\Entity\Modules\Payments\MyPaymentsBillsItems as MyPaymentsBillsItemsEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsBillsItems extends AbstractType {

    const KEY_AMOUNT    = 'amount';
    const KEY_NAME      = 'name';
    const KEY_BILL      = 'bill';
    const KEY_SUBMIT    = 'submit';
    const KEY_DATE      = 'date';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $bills = $this->app->repositories->myPaymentsBillsRepository->getAllNotDeleted();

        $builder
            ->add(static::KEY_AMOUNT, NumberType::class, [
                "attr"  => [
                    'min'  => 0.1,
                    "step" => 0.01,
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.amount'),
                "html5" => true,
            ])
            ->add(static::KEY_NAME, null, [

                'label' => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.name')
            ])
            ->add(static::KEY_DATE, DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.date'),
                'html5'     => false,
            ])
            ->add(static::KEY_BILL, EntityType::class, [
                'class'         => MyPaymentsBillsEntity::class,
                'choices'       => $bills,
                'choice_label'  => function (MyPaymentsBillsEntity $payment_type) {
                    return $payment_type->getName();
                },
                'attr' => [
                    'required' => true,
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.bill')
            ]);

            $builder->add(static::KEY_SUBMIT, SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsBillsItemsEntity::class,
        ]);
    }
}
