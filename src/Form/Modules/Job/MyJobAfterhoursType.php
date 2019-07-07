<?php

namespace App\Form\Modules\Job;

use App\Entity\Modules\Job\MyJobAfterhours;
use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MyJobAfterhoursType extends AbstractType {

    static $choices;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices = (is_array($options) ? $options['goals'] : []);

        $builder
            ->add('Date', null, [
                'attr' => [
                    'data-provide' => "datepicker",
                    'data-date-format' => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete' => 'off'
                ],
                'data' => date('Y-m-d')
            ])
            ->add('Description', null, [
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('Minutes', null, [
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ]);

        if(!empty(static::$choices)){

            $builder
                ->add('Goal', DatalistType::class, [
                    'choices'  => $options['goals'],
                    'required' => false
                ])
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::postSubmit($event);
                })
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::preSubmit($event, ['Goal'], static::$choices);
                });

        }else{
            $builder
                ->add('Goal', TextType::class, [
                    'required' => false
                ]);
        }

        $builder->add('Type', ChoiceType::class, [
            'choices' => $options['entity_enums'],
            'attr' => [
                'autocomplete' => 'off'
            ]
        ]);
        $builder->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyJobAfterhours::class,
        ]);
        $resolver->setRequired('entity_enums');
        $resolver->setRequired('goals');
    }

}
