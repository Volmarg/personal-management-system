<?php

namespace App\Form\Modules\Car;

use App\Controller\Utils\Application;
use App\Entity\Modules\Car\MyCar;
use App\Entity\Modules\Car\MyCarSchedulesTypes;
use App\Services\Translator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MyCarSchedule extends AbstractType {

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
                'label' => $this->app->translator->translate('forms.MyCarSchedule.name'),
            ])
            ->add('Date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget' => 'single_text',
                'format' => 'y-M-d',
                'label'  => $this->app->translator->translate('forms.MyCarSchedule.date'),
            ])
            ->add('Information', null, [
                'label' => $this->app->translator->translate('forms.MyCarSchedule.information'),
            ])
            ->add('scheduleType', EntityType::class, [
                'label'         => $this->app->translator->translate('forms.MyCarSchedule.scheduleType'),
                'class'         => MyCarSchedulesTypes::class,
                'choices'       => $this->app->repositories->myCarSchedulesTypesRepository->findBy(['deleted' => 0]),
                'choice_label'  => function (MyCarSchedulesTypes $schedule_types) {
                    return $schedule_types->getName();
                },
                'required'      => false,

            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyCar::class,
        ]);
    }
}
