<?php

namespace App\Twig\System;

use App\Controller\System\SecurityController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Security extends AbstractExtension {

    /**
     * @var SecurityController $securityController
     */
    private SecurityController $securityController;

    public function __construct(SecurityController $securityController) {
        $this->securityController = $securityController;
    }

    public function getFunctions() {
        return [
            new TwigFunction('canRegisterUser', [$this, 'canRegisterUser']),
        ];
    }

    /**
     * Returns the information if it's allowed to register user in system
     *
     * @return bool
     */
    public function canRegisterUser(): bool
    {
        return $this->securityController->canRegisterUser();
    }

}