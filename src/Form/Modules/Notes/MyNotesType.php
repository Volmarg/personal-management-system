<?php

namespace App\Form\Modules\Notes;

use App\Controller\Utils\Application;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyNotesType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('Title', null, [
                'label' => $this->app->translator->translate('forms.MyNotesType.labels.title')
            ])
            ->add('Body', TextareaType::class, [
                'attr' => [
                    'class' => 'tiny-mce',
                ],
                'required' => false,
                'label' => $this->app->translator->translate('forms.MyNotesType.labels.body')
            ])
            ->add('category', EntityType::class, [
                'class' => MyNotesCategories::class,
                'choices' => $this->app->repositories->myNotesCategoriesRepository->getNotDeletedAndNotLocked(),
                'choice_label' => function (MyNotesCategories $note_category) {
                    return $note_category->getName();
                },
                'label' => $this->app->translator->translate('forms.MyNotesType.labels.category')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyNotes::class,
        ]);
    }

}
