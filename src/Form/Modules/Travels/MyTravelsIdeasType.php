<?php

namespace App\Form\Modules\Travels;

use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use App\Services\Translator;
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
        $translator = new Translator();

        $builder
            ->add('location')
            ->add('country')
            ->add('image', null,[
                'attr'  => [
                    'placeholder' => $translator->translate('forms.MyTravelsIdeasType.placeholders.image')
                ]
            ])
            ->add('map', null, [
                'attr' => [
                    'required'      => false,
                    'placeholder'   => $translator->translate('forms.MyTravelsIdeasType.placeholders.map')
                ]
            ]);

            if(!empty(static::$choices)){
                $builder
                    ->add('category', DatalistType::class, [
                        'choices' => static::$choices,
                        'attr'    => [
                            'placeholder' => $translator->translate('forms.MyTravelsIdeasType.placeholders.category.exists')
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
                            'placeholder' => $translator->translate('forms.MyTravelsIdeasType.placeholders.category.first')
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
