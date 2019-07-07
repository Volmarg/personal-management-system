<?php

namespace App\Form\Modules\Notes;

use App\Controller\Utils\Application;
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
    private static $app;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class)
            ->add('parent_id', EntityType::class, [
                'class' => MyNotesCategories::class,
                'choices' => static::$app->repositories->myNotesCategoriesRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyNotesCategories $note_category) {
                    return $note_category->getName();
                },
                'required' => false,
                'label' => 'Parent'
            ])
            ->add('icon', FontawesomepickerType::class, [])
            ->add('color', ColorType::class, [
                'attr' => [
                    'style' => 'height:40px !important; width:80px !important;'
                ]
            ])
            ->add('submit', SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyNotesCategories::class,
        ]);
    }
}
