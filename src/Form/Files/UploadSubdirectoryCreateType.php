<?php

namespace App\Form\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use App\Services\Files\FilesHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadSubdirectoryCreateType extends AbstractType
{

    const FORM_NAME = 'upload_subdirectory_create';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(FileUploadController::KEY_UPLOAD_MODULE_DIR, ChoiceType::class, [
                'choices' => FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES,
                'attr'    => [
                    'class'                         => 'form-control listFilterer',
                    'data-dependent-list-selector'  => '#upload_subdirectory_create_subdirectory_target_path_in_module_upload_dir'
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryCreateType.labels.uploadModuleDir')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_NAME, TextType::class, [
                'attr'  => [
                    'placeholder' => $this->app->translator->translate('forms.UploadSubdirectoryCreateType.placeholders.subdirectoryName')
                ],
                'label' => $this->app->translator->translate('forms.UploadSubdirectoryCreateType.labels.subdirectoryName')
            ])
            ->add(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, UploadrecursiveoptionsType::class, [
                'choices'   => [], //this is not used anyway but parent ChoiceType requires it
                'required'  => true,
                'label'     => $this->app->translator->translate('forms.UploadSubdirectoryCreateType.labels.subdirectoryInModuleUploadDir')

            ])
            ->add('submit', SubmitType::class, [
                'label'     => $this->app->translator->translate('forms.general.submit')
            ]);

        /**
         * INFO: this is VERY IMPORTANT to use it here due to the difference between data passed as choice
         * and data rendered in field view
         */
        $builder->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
