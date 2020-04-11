<?php

namespace App\Form\Files;

use App\Controller\Core\Application;
use App\Services\Files\FileTagger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateTagsType extends AbstractType
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
            ->add(FileTagger::KEY_TAGS, TextType::class, [
                'required'  => true,
                'attr'      => [
                    'data-value'     => $options[FileTagger::KEY_TAGS]
                ],
                'label' => $this->app->translator->translate('forms.UpdateTagsType.labels.tags')
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->app->translator->translate('forms.general.submit')
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setRequired(FileTagger::KEY_TAGS);
    }
}
