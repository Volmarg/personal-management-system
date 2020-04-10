<?php

namespace App\Form\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MySchedule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Modules\Schedules\MyScheduleType as MyScheduleTypeEntity;

class MyScheduleType extends AbstractType {

    const KEY_PARAM_SCHEDULES_TYPES = 'schedules_type';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $schedules_type = $options['schedules_type'];

        $builder
            ->add('Name', null, [
                'label' => $this->app->translator->translate('forms.MyScheduleType.name'),
            ])
            ->add('Date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'   => 'single_text',
                'format'   => 'y-M-d',
                'label'    => $this->app->translator->translate('forms.MyScheduleType.date'),
                'required' => false
            ])
            ->add('Information', null, [
                'label' => $this->app->translator->translate('forms.MyScheduleType.information'),
            ])
            ->add('scheduleType', EntityType::class, [
                'attr'          => [
                    'class' => 'd-none'
                ],
                'label_attr' => [
                    'class' => 'd-none'
                ],
                'label'         => $this->app->translator->translate('forms.MyScheduleType.scheduleType'),
                'class'         => MyScheduleTypeEntity::class,
                'choices'       => $this->app->repositories->myScheduleTypeRepository->findBy(['name' => $schedules_type, 'deleted' => 0]),
                'choice_label'  => function (MyScheduleTypeEntity $schedule_types) {
                    return $schedule_types->getName();
                },
                'required'      => true,

            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MySchedule::class,
        ]);

        $resolver->setRequired(self::KEY_PARAM_SCHEDULES_TYPES);
    }
}
