<?php


namespace App\Services\Session;


use App\Controller\Core\Application;
use App\Entity\User;
use App\Listeners\OnKernelRequestListener;
use App\VO\Session\SingleSessionKeyLifetimeVO;
use App\VO\Session\AllSessionsKeysLifetimesVO;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Normally data in session will not expire on itself unless for example user will logout, close browser etc.
 * With this service You can add special session `sessions_lifetime` which upon each request is checked
 * -if given session key will be expired then it will be removed from sessions,
 * -if key has not expired then it's lifetime will be extended
 * @see OnKernelRequestListener::handleSessionsLifetimes
 *
 * Class SessionsService
 * @package App\Services\Session
 */
class ExpirableSessionsService extends SessionsService {

    const KEY_SESSIONS_KEYS_LIFETIMES      = "sessions_keys_lifetimes";
    const KEY_SESSION_SYSTEM_LOCK_LIFETIME = "system_lock_lifetime";
    const KEY_SESSION_USER_LOGIN_LIFETIME  = "user_login_lifetime";
    const KEY_SESSION_ALL_SESSION_KEY      = '_sf2_attributes';

    /**
     * SessionsService constructor.
     * @param SessionInterface $session
     * @param Application $app
     * @throws Exception
     */

    /**
     * @param Request|null $request - for handling ajax call response being built from special data stored in session
     * @throws Exception
     */
    public function handleSessionExpiration(?Request $request = null)
    {
        $expired_single_session_key_lifetimes_vo = $this->unsetExpiredSessionsKeysLifetimesAndRefreshRemaining();
        $this->storeDataInSessionForAjaxCall($request, $expired_single_session_key_lifetimes_vo);
        $this->removeExpiredSessionsKeysFromSession($expired_single_session_key_lifetimes_vo);
    }

    /**
     * Adds session lifetime data to the session key
     * if data for given key exists then it will be replaced with new data
     * @param string $session_key
     * @param int $session_lifetime
     * @param array $remove_session_stored_roles
     * @throws Exception
     */
    public function addSessionLifetime(string $session_key, int $session_lifetime, array $remove_session_stored_roles = []): void
    {
        $sessions_lifetime_vo = $this->getSessionsKeysLifetimes();
        $session_lifetime_vo  = new SingleSessionKeyLifetimeVO();

        $session_lifetime_vo->setSessionKey($session_key);
        $session_lifetime_vo->setSessionStartTimestamp($this->now->getTimestamp());
        $session_lifetime_vo->setSessionLifetime($session_lifetime);
        $session_lifetime_vo->setSessionStoredRolesToRemove($remove_session_stored_roles);

        $sessions_lifetime_vo->addSessionLifetimeVO($session_lifetime_vo);

        $this->setSessionsLifetime($sessions_lifetime_vo);
    }

    /**
     * Set data in session alongside with the expiration period
     *  upon expiration it will be automatically invalidated
     * @param string $key
     * @param string $value
     * @param int $session_lifetime
     * @param array $remove_session_stored_roles
     * @throws Exception
     */
    public function addExpirableSession(string $key, string $value, int $session_lifetime, array $remove_session_stored_roles = []): void
    {
        $this->session->set($key, $value);
        $this->addSessionLifetime($key, $session_lifetime, $remove_session_stored_roles);
    }

    /**
     * Will remove session data for key, and invalidate the expirable session record in session for this key
     * @param string $key
     * @throws Exception
     */
    public function removeExpirableSession(string $key): void
    {
        $this->session->remove($key);
        $this->removeExpiredSessionsKeysFromSession([$key]);
    }

    /**
     * If false - then no data in session and no expirable key
     * If true  - then data and expirable key exists in session
     * If null  - then expirable session key exists but data for key does not
     * @param string $key
     * @return bool|null
     * @throws Exception
     */
    public function hasExpirableSession(string $key): ?bool
    {
        $sessions_lifetime_vo  = $this->getSessionsKeysLifetimes();
        $has_expirable_session = $sessions_lifetime_vo->hasSingleSessionKeyLifetime($key);

        if( $has_expirable_session ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Returns all keys that are in the session (might also contain the one with user provider etc)
     * @return string[]
     */
    public static function getAllSessionKeys(): array
    {
        if( empty($_SESSION) ){
            return [];
        }

        $keys = array_keys($_SESSION[self::KEY_SESSION_ALL_SESSION_KEY]);
        return $keys;
    }

    /**
     * Returns all sessions lifetime from session
     * @return AllSessionsKeysLifetimesVO
     * @throws Exception
     */
    private function getSessionsKeysLifetimes(): AllSessionsKeysLifetimesVO
    {
        $is_sessions_lifetime_defined = $this->session->has(self::KEY_SESSIONS_KEYS_LIFETIMES);

        if( $is_sessions_lifetime_defined ){
            $json                 = $this->session->get(self::KEY_SESSIONS_KEYS_LIFETIMES);
            $sessions_lifetime_vo = AllSessionsKeysLifetimesVO::fromJson($json);
        }else{
            $sessions_lifetime_vo = new AllSessionsKeysLifetimesVO();
        }

        return $sessions_lifetime_vo;
    }

    /**
     * This function removes expired key from the global session
     * @param SingleSessionKeyLifetimeVO[] $expired_single_session_key_lifetimes_vo
     * @throws Exception
     */
    private function removeExpiredSessionsKeysFromSession(array $expired_single_session_key_lifetimes_vo): void
    {
        foreach( $expired_single_session_key_lifetimes_vo as $single_session_key_lifetime_vo ){
            $removed_session_key = $single_session_key_lifetime_vo->getSessionKey();

            if( $single_session_key_lifetime_vo->doRemoveSessionStoredRolesInsteadOfSessionKey() ){
                $session_based_roles_to_remove = $single_session_key_lifetime_vo->getSessionStoredRolesToRemove();
                UserRolesSessionService::removeRolesFromSession($session_based_roles_to_remove);
                $message = $this->app->translator->translate('logs.sessions.removingRolesFromSession');
            }elseif( !$this->session->has($removed_session_key) ){
                $message = $this->app->translator->translate("logs.sessions.noSessionDataWasFoundForGivenKey");
            }else{
                $message             = $this->app->translator->translate('logs.sessions.sessionsDataForGivenKeyHasExpired');
                $this->session->remove($removed_session_key);
            }

            $this->app->logger->info($message, [$single_session_key_lifetime_vo->toJson()]);
        }
    }

    /**
     * This function clears the expired sessions data from sessions_keys_lifetimes
     * and refreshes these that didnt expired
     * @return SingleSessionKeyLifetimeVO[]
     * @throws Exception
     */
    private function unsetExpiredSessionsKeysLifetimesAndRefreshRemaining(): array
    {
        $all_keys_in_session = self::getAllSessionKeys();

        $all_sessions_keys_lifetimes                 = $this->getSessionsKeysLifetimes();
        $expired_single_session_key_lifetimes_vo     = $all_sessions_keys_lifetimes->getExpiredSessionsKeysLifetimes();
        $non_expired_single_session_key_lifetimes_vo = $all_sessions_keys_lifetimes->getNonExpiredSessionsKeysLifetimes();

        $all_sessions_keys_lifetimes->unsetExpiredSingleSessionsKeysLifetimes($all_keys_in_session);

        foreach( $non_expired_single_session_key_lifetimes_vo as &$single_session_key_lifetime ){
            $single_session_key_lifetime->resetSessionStartTime();
        }

        $reseted_all_active_sessions_keys_lifetimes = clone $all_sessions_keys_lifetimes;
        $reseted_all_active_sessions_keys_lifetimes->setSingleSessionsKeysLifetimes($non_expired_single_session_key_lifetimes_vo);

        $this->setSessionsLifetime($reseted_all_active_sessions_keys_lifetimes);

        return $expired_single_session_key_lifetimes_vo;
    }

    /**
     * Sets all sessions lifetime to session
     * @param AllSessionsKeysLifetimesVO $all_sessions_keys_lifetimes
     * @throws Exception
     */
    private function setSessionsLifetime(AllSessionsKeysLifetimesVO $all_sessions_keys_lifetimes): void
    {
        $json = $all_sessions_keys_lifetimes->toJson();
        $this->session->set(self::KEY_SESSIONS_KEYS_LIFETIMES, $json);
    }

    /**
     * @param Request|null $request
     * @param SingleSessionKeyLifetimeVO[] $expired_single_session_key_lifetimes_vo
     */
    private function storeDataInSessionForAjaxCall(?Request $request, array $expired_single_session_key_lifetimes_vo): void
    {

        if( is_null($request) ){
            return;
        }

        foreach($expired_single_session_key_lifetimes_vo as $single_session_lifetime_vo){
            $session_key = $single_session_lifetime_vo->getSessionKey();

            // force reload page after invalidating system unlock
            if(
                    $session_key === ExpirableSessionsService::KEY_SESSION_SYSTEM_LOCK_LIFETIME
                &&  UserRolesSessionService::hasRole(User::ROLE_PERMISSION_SEE_LOCKED_RESOURCES
                ) ){
                $message = $this->app->translator->translate('messages.lock.unlockExpiredReloadingPage');

                AjaxCallsSessionService::setPageReloadAfterAjaxCall(true);
                AjaxCallsSessionService::setPageReloadMessageAfterAjaxCall($message);
            }
        }
    }

}