<?php

namespace App\Form\Modules\Payments;

use App\Action\Modules\Payments\MyPaymentsSettingsAction;
use App\Controller\Core\Application;
use App\Entity\Modules\Payments\MyPaymentsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyPaymentsTypesType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', HiddenType::class, [
                'label' => $this->app->translator->translate('forms.MyPaymentsTypesType.labels.name'),
                "attr" => [
                    "value" => MyPaymentsSettingsAction::KEY_SETTING_NAME_TYPE
                ]
            ])
            ->add('value', null, [
                'label' => $this->app->translator->translate('forms.MyPaymentsTypesType.labels.value')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.add')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyPaymentsSettings::class,
        ]);
    }
}
