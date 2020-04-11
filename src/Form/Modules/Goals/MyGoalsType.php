<?php

namespace App\Form\Modules\Goals;

use App\Controller\Core\Application;
use App\Entity\Modules\Goals\MyGoals;
use App\Form\Type\RoundcheckboxType;
use App\Services\Core\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyGoalsType extends AbstractType
{

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
            ->add('name', null, [
                'label' => $this->app->translator->translate('forms.MyGoalsType.name'),
            ])
            ->add('description', null, [
                'label' => $this->app->translator->translate('forms.MyGoalsType.description'),
            ])
            ->add('displayOnDashboard',RoundcheckboxType::class,[
                'label'     => $this->app->translator->translate('forms.MyGoalsType.displayOnDashboard'),
                'required'  => false
            ])
            ->add('submit', SubmitType::class,[
                'label' => $this->app->translator->translate('forms.general.submit'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyGoals::class,
        ]);
    }
}
