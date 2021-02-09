<?php

namespace App\Services\Session;

use Symfony\Component\HttpFoundation\Session\Session;

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
class UserRolesSessionService extends SessionsService {

    const KEY_SESSION_USER_ROLES = "pms_user_roles";

    /**
     * Get all the roles saved in the session
     * @return array|null
     */
    public static function getRolesFromSession():? array
    {
        $session   = new Session();
        $rolesJson = $session->get(self::KEY_SESSION_USER_ROLES);

        if( empty($rolesJson) ){
            return [];
        }

        $rolesArray = json_decode($rolesJson, true);
        return $rolesArray;
    }

    /**
     * Remove given roles from session, if given role does not exist in session then nothing will be done with it
     * @param array $rolesToRemove
     */
    public static function removeRolesFromSession(array $rolesToRemove): void
    {
        $session   = new Session();
        $rolesJson = $session->get(self::KEY_SESSION_USER_ROLES);

        if( !empty($rolesJson) ){
            $rolesArray = json_decode($rolesJson);

            foreach($rolesToRemove as $roleToRemove ){
                if( in_array($roleToRemove, $rolesArray) ){
                    $valueKey = array_search($roleToRemove, $rolesArray);
                    unset($rolesArray[$valueKey]);
                }
            }

            self::saveRolesInSession($rolesArray);
        }
    }

    /**
     * Adds given roles to the session, if role is already in session then it's being skipped - no duplication
     * @param array $roles
     */
    public static function addRolesToSession(array $roles): void
    {
        $session = new Session();

        $rolesInSession           = self::getRolesFromSession();
        $rolesAddedToSessionArray = array_merge($rolesInSession, $roles);
        $rolesAddedToSessionJson  = json_encode($rolesAddedToSessionArray);

        $session->set(self::KEY_SESSION_USER_ROLES, $rolesAddedToSessionJson);
    }

    /**
     * Checks weather the user has the given role in session or not
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        $roles = self::getRolesFromSession();
        return in_array($role, $roles);
    }

    /**
     * This function will save all provided roles into the sessions which means it will replace all currently granted
     * @param array $roles
     */
    private static function saveRolesInSession(array $roles): void
    {
        $session = new Session();

        $rolesJson = json_encode($roles);
        $session->set(self::KEY_SESSION_USER_ROLES, $rolesJson);
    }
}