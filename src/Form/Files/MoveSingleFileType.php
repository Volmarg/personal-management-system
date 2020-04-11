<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use App\Services\Files\FilesHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveSingleFileType extends AbstractType
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR, ChoiceType::class, [
                'choices' => FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES,
                'attr'    => [
                    'class'                        => 'form-control listFilterer',
                    'data-dependent-list-selector' => '#move_single_file_target_subdirectory_path'
                ],
                'label' => $this->app->translator->translate('forms.MoveSingleFileType.labels.targetUploadModuleDir')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
                'label' => $this->app->translator->translate('forms.MoveSingleFileType.labels.targetUploadSubdirectoryInModuleDir')
            ])
            ->add('submit', SubmitType::class, [
            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setRequired(FilesHandler::KEY_MODULES_NAMES);
    }
}
