<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use App\Form\Type\UploadrecursiveoptionsType;
use App\Services\FilesHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryMoveDataType extends AbstractType
{
    const FIELD_REMOVE_CURRENT_FOLDER = 'remove_current_folder';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(FilesHandler::KEY_CURRENT_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_TYPES,
                'attr'    => [
                    'class'                        => 'form-control listFilterer',
                    'data-dependent-list-selector' => '#upload_subdirectory_move_data_subdirectory_current_path_in_upload_dir'
                ]
            ])
            ->add(FilesHandler::KEY_TARGET_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_TYPES,
                'attr'    => [
                    'class'                        => 'form-control listFilterer',
                    'data-dependent-list-selector' => '#upload_subdirectory_move_data_subdirectory_target_path_in_upload_dir'
                ]
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
            ])
            ->add(static::FIELD_REMOVE_CURRENT_FOLDER, CheckboxType::class,[
                'label'     => 'Remove current folder after moving the data?',
                'required'  => false
            ])
            ->add('submit', SubmitType::class, [

            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_UPLOAD_DIR)->resetViewTransformers();
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_UPLOAD_DIR)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
