<?php

namespace App\Services\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * This class was implemented due to high (good written) logic for userRefresh which is impossible to bypass
 *  (well it is but tried few solutions for few hours), the best one - cleanest would be implementing new
 *   authentication provider logic - I don't want this due to already fine working auth.
 *
 * This logic then is required for setting/removing the roles WITHOUT need to reload user and therefore since
 *  we store this in session, the roles are stripped after logout and this is desired effect.
 * Class UserSessionService
 * @package App\Services\Session
 */
class UserSessionService {

    const KEY_SESSION_USER_ROLES = "pms_user_roles";

    /**
     * @var SessionInterface $session
     */
    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    public function getRolesFromSession():? array
    {

    }

    public function removeRolesFromSession(array $roles): void
    {

    }

    public function addRolesToSession(array $roles): void
    {

    }
}