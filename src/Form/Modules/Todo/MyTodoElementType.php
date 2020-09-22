<?php

namespace App\Form\Modules\Todo;

use App\Controller\Core\Application;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\Modules\Todo\MyTodoElement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyTodoElementType extends AbstractType
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
            ->add(MyTodoElement::FIELD_NAME, null, [
               'label' => $this->app->translator->translate('forms.MyTodoElementType.name')
            ])
            ->add(MyTodoElement::FIELD_TODO, EntityType::class, [
                'class'        => MyTodo::class,
                'choices'      => $this->app->repositories->myTodoRepository->findBy([MyTodo::FIELD_DELETED => 0]),
                'choice_label' => function (MyTodo $my_todo) {
                    return $my_todo->getName();
                },
                'label' => $this->app->translator->translate('forms.MyTodoElementType.todo'),
                "attr"  => [
                    "class" => 'selectpicker'
                ]
            ])
            ->add('add', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.add')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyTodoElement::class,
        ]);
    }
}
