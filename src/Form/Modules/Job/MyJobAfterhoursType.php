<?php

namespace App\Form\Modules\Job;

use App\Controller\Core\Application;
use App\Entity\Modules\Job\MyJobAfterhours;
use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MyJobAfterhoursType extends AbstractType {

    static $choices;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices = (is_array($options) ? $options['goals'] : []);

        $builder
            ->add('Date', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget' => 'single_text',
                'format' => 'y-M-d',
                'label' => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.date'),
                "html5" => false,
            ])
            ->add('Description', null, [
                'attr' => [
                    'autocomplete' => 'off'
                ],
                'label' => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.description')
            ])
            ->add('Minutes', NumberType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'min'          => 1
                ],
                'label' => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.minutes'),
                "html5" => true,
            ]);

        if(!empty(static::$choices)){

            $builder
                ->add('Goal', DatalistType::class, [
                    'choices'   => $options['goals'],
                    'required'  => false,
                    'label'     => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.goal')
                ])
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::postSubmit($event);
                })
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::preSubmit($event, ['Goal'], static::$choices);
                });

            /**
             * INFO: this is VERY IMPORTANT to use it here due to S5 validation of data integrity between events
             * This enforce skipping validation for such case - for given field
             */
            $builder->get('Goal')->resetViewTransformers();

        }else{
            $builder
                ->add('Goal', TextType::class, [
                    'required'  => false,
                    'label'     => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.goal')
                ]);
        }

        $builder->add('Type', ChoiceType::class, [
            'choices' => $options['entity_enums'],
            'attr' => [
                'autocomplete' => 'off'
            ],
            'label' => $this->app->translator->translate('forms.MyJobAfterhoursType.labels.type')
        ]);
        $builder->add('submit', SubmitType::class, [
            'label' => $this->app->translator->translate('forms.general.submit')
        ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyJobAfterhours::class,
        ]);
        $resolver->setRequired('entity_enums');
        $resolver->setRequired('goals');
    }

}
