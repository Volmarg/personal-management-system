<?php

namespace App\Form\Modules\Issues;

use App\Controller\Core\Application;
use App\Entity\Modules\Issues\MyIssue;
use App\Form\Type\RoundcheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MyIssueType extends AbstractType {

    const FIELD_DASHBOARD   = 'showOnDashboard';
    const FIELD_NAME        = 'name';
    const FIELD_INFORMATION = 'information';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * 
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(self::FIELD_NAME, TextType::class, [
                'label' => $this->app->translator->translate('forms.MyIssueType.name'),
            ])
            ->add(self::FIELD_INFORMATION, TextType::class, [
                'label' => $this->app->translator->translate('forms.MyIssueType.information'),
            ])
            ->add(self::FIELD_DASHBOARD, RoundcheckboxType::class, [
                'label'    => $this->app->translator->translate('forms.MyIssueType.dashboard'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MyIssue::class,
        ]);
    }
}
