<?php

namespace App\Form\Modules\Schedules;

use App\Controller\Modules\ModulesController;
use App\Controller\Core\Application;
use App\Form\Type\FontawesomepickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Modules\Schedules\MyScheduleType;

class MyScheduleTypeType extends AbstractType {

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
                'label' => $this->app->translator->translate('forms.MyScheduleTypeType.labels.name')
            ])
            ->add('icon', FontawesomepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyScheduleTypeType.labels.icon')
            ])
            ->add('submit', SubmitType::class,[
                'label' => $this->app->translator->translate('forms.general.submit'),
                'attr'  => [
                    'data-params' => '{ "menuNodeModuleName" : "' . ModulesController::MENU_NODE_MODULE_NAME_MY_SCHEDULES .'" }'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyScheduleType::class,
        ]);
    }
}
