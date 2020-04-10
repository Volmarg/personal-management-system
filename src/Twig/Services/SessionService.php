<?php

namespace App\Twig\Services;

use App\Controller\Core\Application;
use App\Services\Session\UserRolesSessionService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SessionService extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var UserRolesSessionService $roles_session_service
     */
    private $roles_session_service;

    public function __construct(Application $app, UserRolesSessionService $rolesSessionService)
    {
        $this->app                   = $app;
        $this->roles_session_service = $rolesSessionService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getRolesSessionService', [$this, 'getRolesSessionService']),
        ];
    }

    public function getRolesSessionService()
    {
        return $this->roles_session_service;
    }

}