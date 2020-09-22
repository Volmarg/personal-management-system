<?php

namespace App\Form\Modules\Notes;

use App\Controller\Modules\Notes\MyNotesCategoriesController;
use App\Controller\Core\Application;
use App\DTO\ParentChildDTO;
use App\Entity\Modules\Notes\MyNotes;
use App\Form\Type\IndentchoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyNotesType extends AbstractType {

    const KEY_CATEGORY = 'category';
    const KEY_TITLE    = 'Title';
    const KEY_BODY     = "Body";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyNotesCategoriesController $my_notes_categories_controller
     */
    private $my_notes_categories_controller;

    public function __construct(Application $app, MyNotesCategoriesController $my_notes_categories_controller) {
        $this->app = $app;
        $this->my_notes_categories_controller = $my_notes_categories_controller;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $choices = $this->buildChoices();

        $builder
            ->add(self::KEY_TITLE, null, [
                'label' => $this->app->translator->translate('forms.MyNotesType.labels.title')
            ])
            ->add(self::KEY_BODY, TextareaType::class, [
                'attr' => [
                    'class' => 'tiny-mce',
                ],
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyNotesType.labels.body')
            ])
            ->add(self::KEY_CATEGORY, IndentchoiceType::class, [
                'choices' => $choices,
                "data"    => false,    // this skips some internal validation for choices and allows to save strings, not just int
                'label'   => $this->app->translator->translate('forms.MyNotesType.labels.category'),
                "attr"    => [
                    "class" => 'selectpicker col-12 p-0'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ])
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event){
                $form_data   = $event->getData();
                $title       = $form_data[self::KEY_TITLE];
                $body        = $form_data[self::KEY_BODY];
                $category_id = $form_data[self::KEY_CATEGORY];

                $category    = $this->app->repositories->myNotesCategoriesRepository->find($category_id);

                $my_note = new MyNotes();
                $my_note->setCategory($category);
                $my_note->setTitle($title);
                $my_note->setBody($body);

                $event->setData($my_note);
            })
            ->get(self::KEY_CATEGORY)->resetViewTransformers();

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
        ]);
    }

    /**
     * @return ParentChildDTO[]
     */
    private function buildChoices(): array
    {
        $parents_children_dtos = $this->my_notes_categories_controller->buildParentsChildrenCategoriesHierarchy();
        return $parents_children_dtos;
    }

}
