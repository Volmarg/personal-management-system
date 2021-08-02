<?php

namespace App\Form;

use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use Doctrine\DBAL\Driver\Exception;
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
    private $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->controllers = $controllers;
        $this->app         = $app;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(FileUploadController::KEY_UPLOAD_MODULE_DIR, ChoiceType::class,[
                'choices'       => $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule(),
                'attr'          => [
                    'data-dependent-list-selector' => '#upload_form_subdirectory'
                ],
                'label' => $this->app->translator->translate('forms.UploadFormType.labels.uploadModuleDir')
            ])
            ->add('file', FileType::class, [
                'multiple' => true,
                'label' => $this->app->translator->translate('forms.UploadFormType.labels.file')
            ]);

        $builder
            ->add('subdirectory', UploadrecursiveoptionsType::class, [
                'choices'     => [], //this is not used anyway but parent ChoiceType requires it,
                'required'    => false,
                'attr' => [
                    'class' => 'form-control align-self-center',
                    'style' => 'height:50px;',
                ],
                'label' => $this->app->translator->translate('forms.UploadFormType.labels.subdirectory')
            ]);

        $builder
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'upload-submit btn btn-sm btn-primary',
                    'style' => 'width:100%; margin: 6px 0 0 6px;'
                ],
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);

        $builder
            ->add('resetSelectedFiles', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-sm btn-primary clear-selection col-1',
                    'style' => 'width:100%; margin: 6px 0 6px 0;'
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
