<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use App\Services\FilesHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveSingleFileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(FilesHandler::KEY_TARGET_UPLOAD_TYPE, ChoiceType::class, [
                'choices' => FileUploadController::UPLOAD_BASED_MODULES,
                'attr'    => [
                    'class'                        => 'form-control listFilterer',
                    'data-dependent-list-selector' => '#move_single_file_target_subdirectory_name'
                ]
            ])
            ->add(FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME, ChoiceType::class, [
                'choices' => $options[FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME]
            ])
            ->add('submit', SubmitType::class, [
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setRequired(FilesHandler::KEY_TARGET_SUBDIRECTORY_NAME);
    }
}
