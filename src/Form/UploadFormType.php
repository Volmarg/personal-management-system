<?php

namespace App\Form;

use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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

        $builder
            ->add('upload_type', ChoiceType::class,[
                'choices'       => FileUploadController::UPLOAD_TYPES,
                'attr'          => [
                    'data-dependent-list-selector' => '#upload_form_subdirectory'
                ]
            ])
            ->add('file', FileType::class, [
                'multiple' => true
            ]);

        $builder
            ->add('subdirectory', UploadrecursiveoptionsType::class, [
              'choices'  => static::$grouped_choices,
                'required' => false,
                'attr'     => [
                   'class'        => 'form-control align-self-center',
                   'style'        => 'height:50px;',
                   'placeholder'  => 'Destination subdirectory name'
                ]
            ]);

        $builder
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'upload-submit btn btn-sm btn-primary',
                    'style' => 'width:100%;'
                ]
            ]);

        $builder
            ->add('resetSelectedFiles', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-sm btn-primary clear-selection col-1',
                    'style' => 'width:100%;'
                ],
                "label" => " "
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => null
        ]);
        $resolver->setRequired('subdirectories');
        $resolver->setRequired('grouped_subdirectories');
    }

}
