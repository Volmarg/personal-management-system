<?php

namespace App\Form\Modules\Travels;

use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyTravelsIdeasType extends AbstractType {

    const CATEGORY_LABEL = 'Category';
    static $choices;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices = (is_array($options) ? $options['categories'] : []);

        $builder
            ->add('location')
            ->add('country')
            ->add('image', null,[
                'attr'  => [
                    'placeholder' => 'Link to image'
                ]
            ])
            ->add('map', null, [
                'attr' => [
                    'required'      => false,
                    'placeholder'   => 'Link to google maps'
                ]
            ]);

            if(!empty(static::$choices)){
                $builder
                    ->add('category', DatalistType::class, [
                        'choices' => static::$choices,
                        'attr'    => [
                            'placeholder' => 'Either pick category from list or type name of new one'
                        ]
                    ])
                    ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                        DatalistLogicOverride::postSubmit($event);
                    })
                    ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                        DatalistLogicOverride::preSubmit($event, ['category'], static::$choices);
                    });
            }else{
                $builder
                    ->add('category', TextType::class, [
                        'attr'    => [
                            'placeholder' => 'Add Your first category'
                        ],
                        'required' => true,
                    ]);
            }

        $builder->add('submit', SubmitType::class);


    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
        $resolver->setRequired('categories');
    }
}
