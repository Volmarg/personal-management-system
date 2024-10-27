<?php

namespace App\Security\UserChecker;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Performs additional checks on user for login logic,
 *
 * {@link https://symfony.com/doc/current/security/user_checkers.html}
 */
class LoginUserChecker implements UserCheckerInterface
{

    /**
     * {@inheritDoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        // nothing here
    }

    /**
     * {@inheritDoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        // nothing here
    }
}