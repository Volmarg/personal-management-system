<?php

namespace App\Form\Modules\Job;

use App\Controller\Utils\Application;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyJobHolidaysType extends AbstractType
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
            ->add('year', ChoiceType::class, [
                'choices'       => $options['choices'],
                'choice_label'  => function ($options) {
                    return $options;
                },
                'attr' => [
                    'required' => 'required'
                ]
            ])
            ->add('daysSpent',IntegerType::class , [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => 'Amount of days that You want to spend'
                ],
                'label' => 'Days',
            ])
            ->add('information', TextType::class, [
                'attr' => [
                    'placeholder' => 'Goal/Reason of spending holidays'
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyJobHolidays::class,
        ]);

        $resolver->setRequired('choices');
    }
}
