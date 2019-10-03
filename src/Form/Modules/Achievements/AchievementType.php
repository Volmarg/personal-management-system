<?php

namespace App\Form\Modules\Achievements;

use App\Controller\Utils\Application;
use App\Entity\Modules\Achievements\Achievement;
use App\Services\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('Name', null, [
                'label' => $this->app->translator->translate('forms.AchievementType.labels.name')
            ])
            ->add('Description', null, [
                'label' => $this->app->translator->translate('forms.AchievementType.labels.description')
            ])
            ->add('Type', ChoiceType::class, [
                'choices' => $options['enum_types'],
                'label'   => $this->app->translator->translate('forms.AchievementType.labels.type')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Achievement::class,
        ]);
        $resolver->setRequired('enum_types');
    }
}
