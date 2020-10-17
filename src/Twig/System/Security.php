<?php

namespace App\Twig\System;

use App\Controller\System\SecurityController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Security extends AbstractExtension {

    /**
     * @var SecurityController $security_controller
     */
    private SecurityController  $security_controller;

    public function __construct(SecurityController $security_controller) {
        $this->security_controller = $security_controller;
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
        return $this->security_controller->canRegisterUser();
    }

}