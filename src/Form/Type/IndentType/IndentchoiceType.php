<?php

namespace App\Form\Type\IndentType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @package App\Form\Type
 */
class IndentchoiceType extends IndentType {

    public function getParent() {
        return ChoiceType::class;
    }

}