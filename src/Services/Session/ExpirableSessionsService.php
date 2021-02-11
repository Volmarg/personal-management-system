<?php


namespace App\Services\Session;


use App\Controller\Core\Application;
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
        $expiredSingleSessionKeyLifetimesVo = $this->unsetExpiredSessionsKeysLifetimesAndRefreshRemaining();
        $this->removeExpiredSessionsKeysFromSession($expiredSingleSessionKeyLifetimesVo);
    }

    /**
     * Adds session lifetime data to the session key
     * if data for given key exists then it will be replaced with new data
     * @param string $sessionKey
     * @param int $sessionLifetime
     * @param array $removeSessionStoredRoles
     * @throws Exception
     */
    public function addSessionLifetime(string $sessionKey, int $sessionLifetime, array $removeSessionStoredRoles = []): void
    {
        $sessionsLifetimeVo = $this->getSessionsKeysLifetimes();
        $sessionLifetimeVo  = new SingleSessionKeyLifetimeVO();

        $sessionLifetimeVo->setSessionKey($sessionKey);
        $sessionLifetimeVo->setSessionStartTimestamp($this->now->getTimestamp());
        $sessionLifetimeVo->setSessionLifetime($sessionLifetime);
        $sessionLifetimeVo->setSessionStoredRolesToRemove($removeSessionStoredRoles);

        $sessionsLifetimeVo->addSessionLifetimeVO($sessionLifetimeVo);

        $this->setSessionsLifetime($sessionsLifetimeVo);
    }

    /**
     * Set data in session alongside with the expiration period
     *  upon expiration it will be automatically invalidated
     * @param string $key
     * @param string $value
     * @param int $sessionLifetime
     * @param array $removeSessionStoredRoles
     * @throws Exception
     */
    public function addExpirableSession(string $key, string $value, int $sessionLifetime, array $removeSessionStoredRoles = []): void
    {
        $this->session->set($key, $value);
        $this->addSessionLifetime($key, $sessionLifetime, $removeSessionStoredRoles);
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
        $sessionsLifetimeVo  = $this->getSessionsKeysLifetimes();
        $hasExpirableSession = $sessionsLifetimeVo->hasSingleSessionKeyLifetime($key);

        if( $hasExpirableSession ){
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
        $isSessionsLifetimeDefined = $this->session->has(self::KEY_SESSIONS_KEYS_LIFETIMES);

        if( $isSessionsLifetimeDefined ){
            $json               = $this->session->get(self::KEY_SESSIONS_KEYS_LIFETIMES);
            $sessionsLifetimeVo = AllSessionsKeysLifetimesVO::fromJson($json);
        }else{
            $sessionsLifetimeVo = new AllSessionsKeysLifetimesVO();
        }

        return $sessionsLifetimeVo;
    }

    /**
     * This function removes expired key from the global session
     * @param SingleSessionKeyLifetimeVO[] $expiredSingleSessionKeyLifetimesVo
     * @throws Exception
     */
    private function removeExpiredSessionsKeysFromSession(array $expiredSingleSessionKeyLifetimesVo): void
    {
        foreach($expiredSingleSessionKeyLifetimesVo as $singleSessionKeyLifetimeVo ){
            $removedSessionKey = $singleSessionKeyLifetimeVo->getSessionKey();

            if( $singleSessionKeyLifetimeVo->doRemoveSessionStoredRolesInsteadOfSessionKey() ){
                $sessionBasedRolesToRemove = $singleSessionKeyLifetimeVo->getSessionStoredRolesToRemove();
                UserRolesSessionService::removeRolesFromSession($sessionBasedRolesToRemove);
                $message = $this->app->translator->translate('logs.sessions.removingRolesFromSession');
            }elseif( !$this->session->has($removedSessionKey) ){
                $message = $this->app->translator->translate("logs.sessions.noSessionDataWasFoundForGivenKey");
            }else{
                $message             = $this->app->translator->translate('logs.sessions.sessionsDataForGivenKeyHasExpired');
                $this->session->remove($removedSessionKey);
            }

            $this->app->logger->info($message, [$singleSessionKeyLifetimeVo->toJson()]);
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
        $allKeysInSession = self::getAllSessionKeys();

        $allSessionsKeysLifetimes              = $this->getSessionsKeysLifetimes();
        $expiredSingleSessionKeyLifetimesVo    = $allSessionsKeysLifetimes->getExpiredSessionsKeysLifetimes();
        $nonExpiredSingleSessionKeyLifetimesVo = $allSessionsKeysLifetimes->getNonExpiredSessionsKeysLifetimes();

        $allSessionsKeysLifetimes->unsetExpiredSingleSessionsKeysLifetimes($allKeysInSession);

        foreach( $nonExpiredSingleSessionKeyLifetimesVo as &$singleSessionKeyLifetime ){
            $singleSessionKeyLifetime->resetSessionStartTime();
        }

        $resetedAllActiveSessionsKeysLifetimes = clone $allSessionsKeysLifetimes;
        $resetedAllActiveSessionsKeysLifetimes->setSingleSessionsKeysLifetimes($nonExpiredSingleSessionKeyLifetimesVo);

        $this->setSessionsLifetime($resetedAllActiveSessionsKeysLifetimes);

        return $expiredSingleSessionKeyLifetimesVo;
    }

    /**
     * Sets all sessions lifetime to session
     * @param AllSessionsKeysLifetimesVO $allSessionsKeysLifetimes
     * @throws Exception
     */
    private function setSessionsLifetime(AllSessionsKeysLifetimesVO $allSessionsKeysLifetimes): void
    {
        $json = $allSessionsKeysLifetimes->toJson();
        $this->session->set(self::KEY_SESSIONS_KEYS_LIFETIMES, $json);
    }

}