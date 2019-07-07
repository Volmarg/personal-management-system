<?php

namespace App\Form\Modules\Car;

use App\Controller\Utils\Application;
use App\Entity\Modules\Car\MyCar;
use App\Entity\Modules\Car\MyCarSchedulesTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MyCarSchedule extends AbstractType {

    /**
     * @var Application
     */
    private static $app;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('Name')
            ->add('Date', null, [
                'attr' => [
                    'data-provide' => "datepicker",
                    'data-date-format' => "dd-mm-yyyy",
                    'data-date-today-highlight' => true,
                    'autocomplete' => 'off'
                ]
            ])
            ->add('Information')
            ->add('scheduleType', EntityType::class, [
                'label'         => 'Schedule type',
                'class'         => MyCarSchedulesTypes::class,
                'choices'       => static::$app->repositories->myCarSchedulesTypesRepository->findBy(['deleted' => 0]),
                'choice_label'  => function (MyCarSchedulesTypes $schedule_types) {
                    return $schedule_types->getName();
                },
                'required'      => false,

            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyCar::class,
        ]);
    }
}
