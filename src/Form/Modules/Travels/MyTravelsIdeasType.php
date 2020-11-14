<?php

namespace App\Form\Modules\Travels;

use App\Controller\Core\Application;
use App\Entity\Modules\Travels\MyTravelsIdeas;
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

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices = (is_array($options) ? $options['categories'] : []);

        $builder
            ->add('location', null, [
                'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.location')
            ])
            ->add('country', null, [
                'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.country')
            ])
            ->add('image', null,[
                'attr'  => [
                    'placeholder' => $this->app->translator->translate('forms.MyTravelsIdeasType.placeholders.image')
                ],
                'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.image')
            ])
            ->add('map', null, [
                'attr' => [
                    'required'      => false,
                    'placeholder'   => $this->app->translator->translate('forms.MyTravelsIdeasType.placeholders.map')
                ],
                'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.map')
            ]);

            if(!empty(static::$choices)){
                $builder
                    ->add('category', DatalistType::class, [
                        'choices' => static::$choices,
                        'attr'    => [
                            'placeholder' => $this->app->translator->translate('forms.MyTravelsIdeasType.placeholders.category.exists')
                        ],
                        'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.category')
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
                            'placeholder' => $this->app->translator->translate('forms.MyTravelsIdeasType.placeholders.category.first')
                        ],
                        'required' => true,
                        'label'   => $this->app->translator->translate('forms.MyTravelsIdeasType.labels.category')
                    ]);
            }

        $builder->add('submit', SubmitType::class, [
            'label'   => $this->app->translator->translate('forms.general.submit')
        ]);


    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyTravelsIdeas::class,
        ]);
        $resolver->setRequired('categories');
    }
}
