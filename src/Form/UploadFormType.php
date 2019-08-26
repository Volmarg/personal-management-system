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

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

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
                'choices'  => [], //this is not used anyway but parent ChoiceType requires it
                'required' => false,
                'attr'     => [
                    'class'        => 'form-control align-self-center',
                    'style'        => 'height:50px;',
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

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get('subdirectory')->resetViewTransformers();

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }

}
