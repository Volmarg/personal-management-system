<?php

namespace App\Form\Modules\Goals;

use App\Controller\Utils\Application;
use App\Entity\Modules\Goals\MyGoals;
use App\Entity\Modules\Goals\MyGoalsSubgoals;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MySubgoalsType extends AbstractType
{

    /**
     * @var Application
     */
    private static $app;

    public function __construct(Application $app) {
        static::$app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('myGoal', EntityType::class, [
                'class' => MyGoals::class,
                'choices' => static::$app->repositories->myGoalsRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyGoals $my_goal) {
                    return $my_goal->getName();
                }
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyGoalsSubgoals::class,
        ]);
    }
}
