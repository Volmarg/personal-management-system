<?php

namespace App\Form\Files;

use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use App\Form\Type\RoundcheckboxType;
use App\Services\Files\FilesHandler;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryCopyDataType extends AbstractType
{
    const FIELD_REMOVE_CURRENT_FOLDER = 'remove_current_folder';
    const KEY_MOVE_FOLDER             = 'move_folder';

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR, ChoiceType::class, [
                'choices' => $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule(),
                'attr'    => [
                    'class'                        => 'form-control listFilterer selectpicker',
                    'data-dependent-list-selector' => '#upload_subdirectory_copy_data_subdirectory_current_path_in_module_upload_dir',
                    'data-live-search'             => 'true',
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryCopyDataType.labels.currentUploadModuleDir')
            ])
            ->add(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR, ChoiceType::class, [
                'choices' => $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule(),
                'attr'    => [
                    'class'                        => 'form-control listFilterer selectpicker',
                    'data-dependent-list-selector' => '#upload_subdirectory_copy_data_subdirectory_target_path_in_module_upload_dir',
                    'data-live-search'             => 'true',
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryCopyDataType.labels.targetUploadModuleDir')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
                'label'     => $this->app->translator->translate('forms.UploadSubdirectoryCopyDataType.labels.currentSubdirectoryInModuleUploadDir')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
                'label'     => $this->app->translator->translate('forms.UploadSubdirectoryCopyDataType.labels.targetSubdirectoryInModuleUploadDir')
            ])
            ->add(static::KEY_MOVE_FOLDER, RoundCheckboxType::class, [
                'required'  => false,
                'label'     => $this->app->translator->translate('forms.UploadSubdirectoryCopyDataType.labels.moveInsteadOfCopying')
            ])
            ->add('submit', SubmitType::class, [

            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR)->resetViewTransformers();
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
