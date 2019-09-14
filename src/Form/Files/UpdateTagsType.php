<?php

namespace App\Form\Files;

use App\Services\FileTagger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateTagsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add(FileTagger::KEY_TAGS, TextType::class, [
                'required'  => true,
                'attr'      => [
                    'data-value'     => $options[FileTagger::KEY_TAGS]
                ]
            ])
            ->add('submit', SubmitType::class, [
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
