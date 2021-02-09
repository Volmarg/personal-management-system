<?php

namespace App\Form\Modules\Issues;

use App\Controller\Core\Application;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Form\Type\DatetimepickerType;
use App\Form\Type\FontawesomepickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MyIssueContactType extends AbstractType {

    const FIELD_DATE        = 'date';
    const FIELD_ICON        = 'icon';
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
            ->add(self::FIELD_INFORMATION, TextType::class, [
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
            ->add(self::FIELD_DATE, DatetimepickerType::class, [
                'label' => $this->app->translator->translate('forms.MyIssueTypeProgress.date'),
                'attr'  => [
                    'autocomplete' => 'off',
                ],
                'required'  => true,
            ])
            ->add(self::FIELD_ICON, FontawesomepickerType::class, [
                'label'    => $this->app->translator->translate('forms.MyIssueTypeProgress.icon'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class'            => MyIssueContact::class,
            self::OPTION_ENTITY_ID  => null,
        ]);

        $resolver->setRequired([
            self::OPTION_ENTITY_ID
        ]);
    }
}
