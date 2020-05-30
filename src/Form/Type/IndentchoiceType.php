<?php

namespace App\Form\Type;

use App\Controller\Core\Application;
use App\DTO\ParentChildDTO;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Todo: maybe it can be used to replace the UploadRecursiveOptionsType - need to check that later
 * This type can be used to output the choice type with indents
 *  use example
 *   [
 *    0 => [
 *          DTO
 *         ]
 *   ]
 *  The indents are created via DTO "indent" param
 * This name of class MUST be written like this (at least in Symfony 4.x) otherwise symfony won't load twig template for this type
 *
 * Class DatalistType
 * @package App\Form\Type
 */
class IndentchoiceType extends AbstractType {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }


    public function getParent() {
        return ChoiceType::class;
    }


    const KEY_CHOICES = 'choices';

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

        $view->vars[self::KEY_CHOICES] = $options[self::KEY_CHOICES];
    }

}