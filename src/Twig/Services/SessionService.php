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
     * @var UserRolesSessionService $rolesSessionService
     */
    private $rolesSessionService;

    public function __construct(Application $app, UserRolesSessionService $rolesSessionService)
    {
        $this->app                 = $app;
        $this->rolesSessionService = $rolesSessionService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getRolesSessionService', [$this, 'getRolesSessionService']),
        ];
    }

    public function getRolesSessionService()
    {
        return $this->rolesSessionService;
    }

}