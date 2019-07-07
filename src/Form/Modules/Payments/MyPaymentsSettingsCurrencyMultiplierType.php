<?php

namespace App\Form\Modules\Payments;

use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsSettingsCurrencyMultiplierType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $em = $options['em'];

        $builder
            ->add('name', HiddenType::class, [
                'data' => 'currency_multiplier'
            ])
            ->add('value', NumberType::class, [
                'label' => 'Currency multiplier',
                'data' => $em->getRepository(MyPaymentsSettings::class)->fetchCurrencyMultiplier()
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsSettings::class,
        ]);
        $resolver->setRequired('em');
    }

}
