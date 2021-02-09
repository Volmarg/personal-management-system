<?php

namespace App\Form\Modules\Issues;

use App\Controller\Core\Application;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueProgress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MyIssueProgressType extends AbstractType {

    const FIELD_DATE        = 'date';
    const FIELD_ISSUE       = 'issue';
    const FIELD_INFORMATION = 'information';

    const OPTION_ENTITY_ID  = "entity_id";

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

        $entityId = $options[self::OPTION_ENTITY_ID];

        $builder
            ->add(self::FIELD_INFORMATION, TextareaType::class, [
                'attr' => [
                    'class' => 'tiny-mce',
                ],
                'label' => $this->app->translator->translate('forms.MyIssueTypeProgress.information'),
            ])
            ->add(self::FIELD_ISSUE, EntityType::class, [
                'label'         => $this->app->translator->translate('forms.MyIssueTypeProgress.issue'),
                'class'         => MyIssue::class,
                'choices'       => $this->app->repositories->myIssueRepository->findAllNotDeletedAndNotResolved($entityId),
                'choice_label'  => function (MyIssue $issue) {
                    return $issue->getName();
                },
                'required'      => true,
            ])
            ->add(self::FIELD_DATE, DateType::class, [
                'label'    => $this->app->translator->translate('forms.MyIssueTypeProgress.date'),
                'attr' => [
                    'data-provide'              => "datepicker",
                    'data-date-format'          => "yyyy-mm-dd",
                    'data-date-week-start'      => 1,
                    'data-date-today-highlight' => true,
                    'autocomplete'              => 'off',
                ],
                'widget'    => 'single_text',
                'format'    => 'y-M-d',
                'required'  => true,
                'html5'     => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class'            => MyIssueProgress::class,
            self::OPTION_ENTITY_ID  => null,
        ]);

        $resolver->setRequired([
            self::OPTION_ENTITY_ID
        ]);
    }
}
