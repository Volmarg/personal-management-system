<?php

namespace App\Form\Modules\Notes;

use App\Controller\Core\Application;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Form\Type\FontawesomepickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyNotesCategoriesType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', TextType::class, [
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.name')
            ])
            ->add('parent_id', EntityType::class, [
                'class' => MyNotesCategories::class,
                'choices' => $this->app->repositories->myNotesCategoriesRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyNotesCategories $note_category) {
                    return $note_category->getName();
                },
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.parentId'),
                "attr"  => [
                    "class" => 'selectpicker col-12 p-0'
                ]
            ])
            ->add('icon', FontawesomepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyNotesCategoriesType.icon')
            ])
            ->add('color', ColorType::class, [
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
