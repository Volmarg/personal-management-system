<?php

namespace App\Form\Modules\Achievements;

use App\Controller\Core\Application;
use App\Entity\Modules\Achievements\Achievement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType {

    const KEY_OPTION_ENUM_TYPES = "enum_types";

    const KEY_NAME        = "Name";
    const KEY_DESCRIPTION = "Description";
    const KEY_TYPE        = "Type";
    const KEY_SUBMIT      = "submit";

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
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add(self::KEY_NAME, null, [
                'label' => $this->app->translator->translate('forms.AchievementType.labels.name')
            ])
            ->add(self::KEY_DESCRIPTION, null, [
                'label' => $this->app->translator->translate('forms.AchievementType.labels.description')
            ])
            ->add(self::KEY_TYPE, ChoiceType::class, [
                'choices' => $options[self::KEY_OPTION_ENUM_TYPES],
                'label'   => $this->app->translator->translate('forms.AchievementType.labels.type'),
                "attr"    => [
                    "class" => 'selectpicker'
                ]
            ])
            ->add(self::KEY_SUBMIT, SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Achievement::class,
        ]);
        $resolver->setRequired(self::KEY_OPTION_ENUM_TYPES);
    }
}
