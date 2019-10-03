<?php

namespace App\Form\Modules\Car;

use App\Controller\Utils\Application;
use App\Entity\Modules\Car\MyCarSchedulesTypes;
use App\Services\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyCarScheduleType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', null, [
                'label' => $this->app->translator->translate('forms.MyCarScheduleType.labels.name')
            ])
            ->add('submit', SubmitType::class,[
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyCarSchedulesTypes::class,
        ]);
    }
}
