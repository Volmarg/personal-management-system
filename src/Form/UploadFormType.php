<?php

namespace App\Form;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Form\Events\DatalistLogicOverride;
use App\Form\Type\DatalistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

    /**
     * @var array $grouped_choices
     */
    static $grouped_choices;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        static::$choices         = (is_array($options) ? $options['subdirectories'] : []);
        static::$grouped_choices = (is_array($options) ? $options['grouped_subdirectories'] : []);
        $mapped_group_and_values = $this->mapGroupsAndValues();

        $builder
            ->add('upload_type', ChoiceType::class,[
                'choices'       => FileUploadController::UPLOAD_TYPES,
                'attr'          => [
                    'data-dependent-list-selector' => '#upload_form_subdirectoryDatalistType'
                ]
            ])
            ->add('file', FileType::class, [
                'multiple' => true
            ]);

        if(!empty(static::$choices)){

            $builder
                ->add('subdirectory', DatalistType::class, [
                    'choices'     => static::$choices,
                    'choice_attr' => [$mapped_group_and_values],
                    'required' => false,
                    'attr'     => [
                       'class'                      => 'form-control align-self-center',
                       'style'                      => 'height:50px;',
                       'placeholder'                => 'Destination subdirectory name'
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
        $resolver->setRequired('grouped_subdirectories');
    }

    /**
     * This is a bit dirty workaround to pass groups to datalist
     * @return array
     */
    private function mapGroupsAndValues() {
        $mapped_groups_and_values = [];

        foreach(static::$grouped_choices as $group_name => $group_values){

            foreach($group_values as $key => $value){
                $mapped_groups_and_values[$value][] = $group_name;
            }

        }

        return $mapped_groups_and_values;
    }
}
