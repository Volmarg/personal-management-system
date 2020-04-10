<?php

namespace App\Form\Modules\Shopping;

use App\Controller\Core\Application;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyShoppingPlansType extends AbstractType {

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
                'label' => $this->app->translator->translate('forms.MyShoppingPlansType.labels.name')
            ])
            ->add('Information', null, [
                'label' => $this->app->translator->translate('forms.MyShoppingPlansType.labels.information')
            ])
            ->add('Example', null, [
                'label' => $this->app->translator->translate('forms.MyShoppingPlansType.labels.example')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyShoppingPlans::class,
        ]);
    }
}
