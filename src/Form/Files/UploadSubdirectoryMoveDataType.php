<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryMoveDataType extends AbstractType
{
    const FIELD_REMOVE_CURRENT_FOLDER = 'remove_current_folder';

    #TODO: add later js for handling switch upload type - so only subdirectories for given type should be on list
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(FileUploadController::KEY_CURRENT_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_TYPES
            ])
            ->add(FileUploadController::KEY_TARGET_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_TYPES
            ])
            ->add(FileUploadController::KEY_CURRENT_SUBDIRECTORY_NAME, ChoiceType::class, [
                'choices' => $options[FileUploadController::KEY_CURRENT_SUBDIRECTORY_NAME]
            ])
            ->add(FileUploadController::KEY_TARGET_SUBDIRECTORY_NAME, ChoiceType::class, [
                'choices' => $options[FileUploadController::KEY_TARGET_SUBDIRECTORY_NAME]
            ])
            ->add(static::FIELD_REMOVE_CURRENT_FOLDER, CheckboxType::class,[
                'label'     => 'Remove current folder after moving the data?',
                'required'  => false
            ])
            ->add('submit', SubmitType::class, [

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setRequired(FileUploadController::KEY_CURRENT_SUBDIRECTORY_NAME);
        $resolver->setRequired(FileUploadController::KEY_TARGET_SUBDIRECTORY_NAME);
    }
}
