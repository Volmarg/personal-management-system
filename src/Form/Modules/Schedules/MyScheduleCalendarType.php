<?php

namespace App\Form\Modules\Schedules;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use App\Form\Type\FontawesomepickerType;
use App\Form\Type\JscolorpickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyScheduleCalendarType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyScheduleCalendarType.name.label')
            ])
            ->add('icon', FontawesomepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyScheduleCalendarType.icon.label')
            ])
            ->add('color', JscolorpickerType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ],
                'label' => $this->app->translator->translate('forms.MyScheduleCalendarType.color.label')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyScheduleCalendar::class,
        ]);
    }
}
