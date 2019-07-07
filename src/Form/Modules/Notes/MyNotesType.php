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

    public function __construct(Application $app) {
        static::$app = $app;
    }

    /**
     * @var Application
     */
    private static $app;

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('Title')
            ->add('Body', TextareaType::class, [
                'attr' => [
                    'class' => 'tiny-mce',
                ],
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'class' => MyNotesCategories::class,
                'choices' => static::$app->repositories->myNotesCategoriesRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyNotesCategories $note_category) {
                    return $note_category->getName();
                }
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyNotes::class,
        ]);
    }

}
