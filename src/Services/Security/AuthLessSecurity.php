<?php

namespace App\Services\Security;

/**
 * This class is used to handle authentication of access to the pages which are OUT-OF-SYMFONY context
 * At this moment this given logic supports only check if user is logged in, no role support is provided,
 */
class AuthLessSecurity
{

    /**
     * Check if user is logged in by comparing provided session_id (which should be taken from active session)
     * and the session_id stored in cookie.
     *
     * If both are equal we can assume that user is logged in, else if the session_id is provided in cookie
     * and is not present on the server then no user is having such session id active.
     *
     * @param string|null $sessionId
     * @return bool
     */
    public static function isLoggedIn(?string $sessionId): bool
    {
        if( empty($sessionId) ){
            return false;
        }

        $sessionName = ini_get("session.name");
        if( !array_key_exists($sessionName, $_COOKIE) ){
            return false;
        }

        $sessionIdInCookie = $_COOKIE[$sessionName];
        return ($sessionId === $sessionIdInCookie);
    }
}