<?php

namespace App\Form\Modules\Goals;

use App\Controller\Core\Application;
use App\Entity\Modules\Goals\MyGoalsPayments;
use App\Form\Type\RoundcheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyGoalsPaymentsType extends AbstractType {

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
                'label' => $this->app->translator->translate('forms.MyGoalsPaymentsType.name')
            ])
            ->add('collectionStartDate', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $this->app->translator->translate('forms.MyGoalsPaymentsType.collectionStartDate'),
                'html5'     => false,
            ])
            ->add('deadline', DateType::class, [
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off'
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'label'     => $this->app->translator->translate('forms.MyGoalsPaymentsType.deadline'),
                'html5'     => false,
            ])
            ->add('moneyGoal', IntegerType::class,[
                'attr' => [
                    'min' => 1
                ],
                'label' => $this->app->translator->translate('forms.MyGoalsPaymentsType.moneyGoal')
            ])
            ->add('moneyCollected', IntegerType::class, [
                'attr' => [
                    'min' => 1
                ],
                'label' => $this->app->translator->translate('forms.MyGoalsPaymentsType.moneyCollected')
            ])
            ->add('displayOnDashboard',RoundcheckboxType::class,[
                'label'     => $this->app->translator->translate('forms.MyGoalsPaymentsType.displayOnDashboard'),
                'required'  => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyGoalsPayments::class,
        ]);
    }
}
