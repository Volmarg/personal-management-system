<?php

namespace App\Form\Modules\Payments;

use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use App\Form\Type\RoundcheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class MyPaymentsProductsType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('Name', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.name')
            ])
            ->add('Price', NumberType::class, [
                "attr" => [
                    "min"  => 0.01,
                    "step" => 0.01,
                ],
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.price'),
                'html5' => true,
            ])
            ->add('Market', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.market')
            ])
            ->add('Products', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.products')
            ])
            ->add('Information', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.information')
            ])
            ->add('Rejected', RoundcheckboxType::class, [
                'label'     => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.rejected'),
                "required"  => false
            ])
            ->add('save', SubmitType::class,[
                'label' => $this->app->translator->translate('forms.general.save')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsProduct::class,
        ]);
    }
}
