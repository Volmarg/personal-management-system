<?php

namespace App\Form\Type\IndentType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * @package App\Form\Type
 */
class IndententityType extends IndentType {

    public function getParent() {
        return EntityType::class;
    }

}