<?php

namespace App\Form;

use App\Controller\Utils\Application;
use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadFormType extends AbstractType {

    /**
     * @var Application
     */
    private static $app;

    /**
     * @var array $choices
     */
    static $choices;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices = (is_array($options) ? $options['subdirectories'] : []);

        $builder
            ->add('file', FileType::class, [
                'multiple' => true
            ]);

        if(!empty(static::$choices)){

            $builder
                ->add('subdirectory', DatalistType::class, [
                    'choices'  => static::$choices,
                    'required' => false,
                    'attr'     => [
                       'class' => 'form-control align-self-center',
                       'style' => 'height:50px;'
                    ]
                ])
                ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::postSubmit($event);
                })
                ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    DatalistLogicOverride::preSubmit($event, ['subdirectory'], static::$choices);
                });

        }else{
            $builder
                ->add('subdirectory', TextType::class, [
                    'required' => false
                ]);
        }

        $builder
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'upload-submit btn btn-sm btn-primary',
                    'style' => 'width:100%;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => null
        ]);
        $resolver->setRequired('subdirectories');
    }
}
