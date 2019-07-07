<?php

namespace App\Form\Modules\Payments;

use App\Controller\Utils\Application;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsMonthlyType extends AbstractType {

    /**
     * @var Application
     */
    private static $app;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $payments_types = static::$app->repositories->myPaymentsSettingsRepository->findBy(['deleted' => 0, 'name' => 'type']);

        $builder
            ->add('date', null, [
                'attr' => [
                    'data-provide' => "datepicker",
                    'data-date-format' => "dd-mm-yyyy",
                    'data-date-today-highlight' => true,
                    'autocomplete' => 'off'
                ],
                'data' => date('d-m-Y')
            ])
            ->add('money', NumberType::class)
            ->add('description')
            ->add('type', EntityType::class, [
                'class' => MyPaymentsSettings::class,
                'choices' => $payments_types,
                'choice_label' => function (MyPaymentsSettings $payment_type) {
                    return $payment_type->getValue();
                },
                'attr' => [
                    'required' => true,
                ]
            ]);

            $builder->add('save', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsMonthly::class,
        ]);
    }
}
