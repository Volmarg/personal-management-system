<?php

namespace App\Form\Type\IndentType;

use App\Controller\Core\Application;
use App\DTO\ParentChildDTO;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This type can be used to output the choice type with indents
 * The indents are created via DTO "indent" param
 * This name of class MUST be written like this (at least in Symfony 4.x) otherwise symfony won't load twig template for this type
 *
 * @package App\Form\Type
 */
abstract class IndentType extends AbstractType {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @throws Exception
     */
    public function getParent() {
        throw new Exception("Implement Your own parent type logic here!");
    }

    /**
     * This must be used instead of normal choices as for example EntityType expect choices to be entities
     * and thanks to this new key it's possible to both keep choices and use dtos to build menu
     */
    const KEY_CHOICES = 'parent_child_choices';

    /**
     * If this is set then additional empty option is added to the select list
     */
    const KEY_INCLUDE_EMPTY_CHOICE = 'include_empty_choice';

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @throws Exception
     */
    public function buildView(FormView $view, FormInterface $form, array $options) {

        if( !array_key_exists(self::KEY_CHOICES, $options) ){
            $message = $this->app->translator->translate('Types.indentChoice.errors.choicesAreMissing');
            throw new Exception($message);
        }

        $choices         = $options[self::KEY_CHOICES];
        $areChoicesValid = true;
        $message         = "";

        if( !is_array($choices) ){
            $areChoicesValid = false;
            $message         = $this->app->translator->translate('Types.indentChoice.errors.choicesIsNotArray');
        }

        foreach( $choices as $choice ){
            if( !($choice instanceof ParentChildDTO) ){
                $areChoicesValid = false;
                $message         = $this->app->translator->translate('Types.indentChoice.errors.choiceIsNotInstanceOfIndentChoiceTypeDTO');
                continue;
            }
        }

        if( !$areChoicesValid ){
            throw new Exception($message);
        }

        $view->vars[self::KEY_CHOICES]              = $options[self::KEY_CHOICES];
        $view->vars[self::KEY_INCLUDE_EMPTY_CHOICE] = $options[self::KEY_INCLUDE_EMPTY_CHOICE];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::KEY_CHOICES              => [],
            self::KEY_INCLUDE_EMPTY_CHOICE => false,
        ]);
    }
}