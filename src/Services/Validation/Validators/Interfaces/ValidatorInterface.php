<?php

namespace App\Services\Validation\Validators\Interfaces;

use App\Controller\Core\Application;
use App\VO\Validators\ValidationResultVO;

/**
 * Provides basic validation methods
 *
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{

    public function __construct(Application $app);

    /**
     * Validates the object
     *
     * @param object $object
     * @return ValidationResultVO
     */
    public function doValidate(object $object): ValidationResultVO;

}