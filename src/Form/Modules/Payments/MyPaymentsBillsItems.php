<?php

namespace App\Form\Modules\Payments;

use App\Controller\Utils\Application;
use App\Entity\Modules\Payments\MyPaymentsBills as MyPaymentsBillsEntity;
use App\Entity\Modules\Payments\MyPaymentsBillsItems as MyPaymentsBillsItemsEntity;
use App\Entity\Modules\Payments\MyPaymentsMonthly;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $bills = $this->app->repositories->myPaymentsBillsRepository->findBy(['deleted' => 0]);

        $builder
            ->add(static::KEY_AMOUNT, NumberType::class, [
                'label' => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.amount')
            ])
            ->add(static::KEY_NAME, null, [

                'label' => $this->app->translator->translate('forms.MyPaymentsBillsItems.labels.name')
            ])
            ->add(static::KEY_BILL, EntityType::class, [
                'class'         => MyPaymentsBillsEntity::class,
                'choices'       => $bills,
                'choice_label'  => function (MyPaymentsBillsEntity $payment_type) {
                    return $payment_type->getName();
                },
                'attr' => [
                    'required' => true,
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
