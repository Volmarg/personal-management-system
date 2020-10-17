<?php

namespace App\Form\Modules\Job;

use App\Controller\Core\Application;
use App\Controller\Utils\Utils;
use App\Entity\Modules\Job\MyJobHolidays;
use App\Form\Interfaces\ValidableFormInterface;
use App\Services\Core\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyJobHolidaysType extends AbstractType implements ValidableFormInterface
{
    /**
     * @return string
     */
    public static function getFormPrefix(): string {
        return Utils::getClassBasename(MyJobHolidays::class);
    }

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->app->translator = new Translator();

        $builder
            ->add('year', ChoiceType::class, [
                'choices'       => $options['choices'],
                'choice_label'  => function ($options) {
                    return $options;
                },
                'attr' => [
                    'required'      => 'required',
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysType.labels.year'),
            ])
            ->add('daysSpent',IntegerType::class , [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => $this->app->translator->translate('forms.MyJobHolidaysType.placeholders.daysSpent')
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysType.labels.daysSpent'),
            ])
            ->add('information', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyJobHolidaysType.placeholders.information')
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysType.labels.information'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyJobHolidays::class,
        ]);

        $resolver->setRequired('choices');
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string {
        return self::getFormPrefix();
    }
}
