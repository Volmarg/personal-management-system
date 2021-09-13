<?php

namespace App\Form\Files;

use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryRenameType extends AbstractType {

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
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(FileUploadController::KEY_UPLOAD_MODULE_DIR, ChoiceType::class, [
                'choices' => $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule(),
                'attr'    => [
                    'class'                                          => 'form-control listFilterer selectpicker',
                    'data-dependent-list-selector'                   => '#upload_subdirectory_rename_subdirectory_current_path_in_module_upload_dir',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryRenameType.labels.uploadModuleDir')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
                'label'     => $this->app->translator->translate('forms.UploadSubdirectoryRenameType.labels.subdirectoryInModuleUploadDir'),
                'attr'      => [
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                ]
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME, TextType::class, [
                'attr'  => [
                    'placeholder' => $this->app->translator->translate('forms.UploadSubdirectoryRenameType.placeholders.subdirectoryNewName')
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryRenameType.labels.subdirectoryNewName')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
