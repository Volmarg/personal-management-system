<?php

namespace App\Form\Modules\Notes;

use App\Controller\Core\Application;
use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Form\Type\FontawesomepickerType;
use App\Form\Type\IndentType\IndententityType;
use App\Form\Type\IndentType\IndentType;
use App\Form\Type\JscolorpickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyNotesCategoriesType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyNotesCategoriesController $myNotesCategoriesController
     */
    private MyNotesCategoriesController $myNotesCategoriesController;

    public function __construct(Application $app, MyNotesCategoriesController $myNotesCategoriesController) {
        $this->app                         = $app;
        $this->myNotesCategoriesController = $myNotesCategoriesController;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.name')
            ])
            ->add('parent_id', IndententityType::class, [
                'class'                              => MyNotesCategories::class,
                IndentType::KEY_CHOICES              => $this->myNotesCategoriesController->buildParentsChildrenCategoriesHierarchy(),
                IndentType::KEY_INCLUDE_EMPTY_CHOICE => true,
                'choices'               => $this->app->repositories->myNotesCategoriesRepository->findAllNotDeleted(),
                'choice_label'          => function (MyNotesCategories $noteCategory) {
                    return $noteCategory->getName();
                },
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.parentId'),
                "attr"  => [
                    'class'                                          => 'selectpicker',
                    'data-append-classes-to-bootstrap-select-parent' => 'bootstrap-select-width-100',
                    'data-append-classes-to-bootstrap-select-button' => 'm-0',
                    'data-live-search'                               => 'true',
                ]
            ])
            ->add('icon', FontawesomepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.icon')
            ])
            ->add('color', JscolorpickerType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ],
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.color')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyNotesCategories::class,
        ]);
    }
}
