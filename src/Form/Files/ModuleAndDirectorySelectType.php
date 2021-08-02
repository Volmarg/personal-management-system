<?php

namespace App\Form\Files;

use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Form\Type\UploadrecursiveoptionsType;
use Doctrine\DBAL\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is special `form` which consist only of 2 selects (module and directories)
 * Can be used for example just to show options on the frontend and process the selection via js
 * No submit is provided and it should remain this way
 *
 * Class ModuleAndDirectorySelectType
 * @package App\Form
 */
class ModuleAndDirectorySelectType extends AbstractType {

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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(FileUploadController::KEY_UPLOAD_MODULE_DIR, ChoiceType::class,[
                'choices'       => $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule(),
                'attr'          => [
                    'data-dependent-list-selector'                 => '#module_and_directory_select_subdirectory',
                    'data-module-and-directory-select-form-module' => "", // this attribute can be used in js / should never be changed
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
                    'data-module-and-directory-select-form-subdirectory' => "", // this attribute can be used in js / should never be changed
                    'class'                                              => 'form-control align-self-center',
                    'style'                                              => 'height:50px;',
                ],
                'label' => $this->app->translator->translate('forms.UploadFormType.labels.subdirectory')
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
