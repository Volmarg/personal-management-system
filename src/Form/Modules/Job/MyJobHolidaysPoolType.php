<?php

namespace App\Form\Modules\Job;

use App\Controller\Core\Application;
use App\Entity\Modules\Job\MyJobHolidaysPool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyJobHolidaysPoolType extends AbstractType
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
            ->add('year', IntegerType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyJobHolidaysPoolType.placeholders.year')
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysPoolType.labels.year')
            ])
            ->add(MyJobHolidaysPool::FIELD_DAYS_IN_POOL, IntegerType::class, [
                'attr' => [
                    'min'           => 1,
                    'placeholder'   => $this->app->translator->translate('forms.MyJobHolidaysPoolType.placeholders.daysInPool')
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysPoolType.labels.daysInPool')
            ])
            ->add('CompanyName', TextType::class, [
                'attr' => [
                    'placeholder' => $this->app->translator->translate('forms.MyJobHolidaysPoolType.placeholders.companyName')
                ],
                'label' => $this->app->translator->translate('forms.MyJobHolidaysPoolType.labels.companyName')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyJobHolidaysPool::class,
        ]);
    }
}
