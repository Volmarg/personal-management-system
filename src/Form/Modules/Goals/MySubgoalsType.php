<?php

namespace App\Form\Modules\Goals;

use App\Controller\Core\Application;
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
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('name', null, [
               'label' => $this->app->translator->translate('forms.MySubgoalsType.name')
            ])
            ->add('myGoal', EntityType::class, [
                'class'        => MyGoals::class,
                'choices'      => $this->app->repositories->myGoalsRepository->findBy(['deleted' => 0]),
                'choice_label' => function (MyGoals $my_goal) {
                    return $my_goal->getName();
                },
                'label'        => $this->app->translator->translate('forms.MySubgoalsType.myGoal'),
                "attr"         => [
                    "class" => 'selectpicker'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyGoalsSubgoals::class,
        ]);
    }
}
