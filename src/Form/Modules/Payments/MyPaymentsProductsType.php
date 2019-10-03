<?php

namespace App\Form\Modules\Payments;

use App\Controller\Utils\Application;
use App\Entity\Modules\Payments\MyPaymentsProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('Price', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.price')
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
            ->add('Rejected', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsProductsType.labels.rejected')
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
