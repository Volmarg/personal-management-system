<?php

namespace App\VO\Session;

use App\VO\AbstractVO;
use DateTime;
use Exception;

class SingleSessionKeyLifetimeVO extends AbstractVO {

    const KEY_SESSION_KEY                                   = "session_key";
    const KEY_SESSION_START_TIME                            = "session_start_time";
    const KEY_SESSION_LIFETIME                              = "session_lifetime";
    const KEY_SESSION_END_DATE_TIME                         = "session_end_datetime";
    const KEY_SESSION_REMOVE_SESSION_STORED_ROLES_TO_REMOVE = "session_stored_roles_to_remove";

    /**
     * @var string $sessionKey
     */
    private $sessionKey = '';

    /**
     * @var int $sessionStartTimestamp
     */
    private $sessionStartTimestamp = 0;

    /**
     * @var int $sessionLifetime
     */
    private $sessionLifetime;

    /**
     * @var DateTime $sessionEndDatetime
     */
    private $sessionEndDatetime;

    /**
     * @var array $sessionStoredRolesToRemove
     */
    private $sessionStoredRolesToRemove = [];

    /**
     * @return string
     */
    public function getSessionKey(): string {
        return $this->sessionKey;
    }

    /**
     * @param string $sessionKey
     */
    public function setSessionKey(string $sessionKey): void {
        $this->sessionKey = $sessionKey;
    }

    /**
     * @return int
     */
    public function getSessionStartTimestamp(): int {
        return $this->sessionStartTimestamp;
    }

    /**
     * @param int $sessionStartTimestamp
     * @throws Exception
     */
    public function setSessionStartTimestamp(int $sessionStartTimestamp): void {
        $maxLifetimeTimestamp = $sessionStartTimestamp + $this->sessionLifetime;
        $maxLifeDateTime      = new DateTime();
        $maxLifeDateTime->setTimestamp($maxLifetimeTimestamp);

        $this->sessionStartTimestamp = $sessionStartTimestamp;
        $this->sessionEndDatetime    = $maxLifeDateTime;
    }

    /**
     * @throws Exception
     */
    public function resetSessionStartTime(): void
    {
        $this->sessionStartTimestamp = (new DateTime())->getTimestamp();
    }

    /**
     * @return mixed
     */
    public function getSessionLifetime() {
        return $this->sessionLifetime;
    }

    /**
     * @param mixed $sessionLifetime
     */
    public function setSessionLifetime($sessionLifetime): void {
        $this->sessionLifetime = $sessionLifetime;
    }

    /**
     * @throws Exception
     */
    public function getSessionEndDateTime(): DateTime {
        return $this->sessionEndDatetime;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toJson(): string
    {
        $array = $this->toArray();
        $json  = json_encode($array);

        return $json;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        $array = [
            self::KEY_SESSION_KEY                                   => $this->getSessionKey(),
            self::KEY_SESSION_LIFETIME                              => $this->getSessionLifetime(),
            self::KEY_SESSION_START_TIME                            => $this->getSessionStartTimestamp(),
            self::KEY_SESSION_END_DATE_TIME                         => $this->getSessionEndDateTime(),
            self::KEY_SESSION_REMOVE_SESSION_STORED_ROLES_TO_REMOVE => $this->getSessionStoredRolesToRemove(),
        ];

        return $array;
    }

    /**
     * @return array
     */
    public function getSessionStoredRolesToRemove(): array {
        return $this->sessionStoredRolesToRemove;
    }

    /**
     * @param array $sessionStoredRolesToRemove
     */
    public function setSessionStoredRolesToRemove(array $sessionStoredRolesToRemove): void {
        $this->sessionStoredRolesToRemove = $sessionStoredRolesToRemove;
    }

    /**
     * @return bool
     */
    public function doRemoveSessionStoredRolesInsteadOfSessionKey(): bool
    {
        return !empty($this->sessionStoredRolesToRemove);
    }

    /**
     * @param string $json
     * @return SingleSessionKeyLifetimeVO
     * @throws Exception
     */
    public static function fromJson(string $json): SingleSessionKeyLifetimeVO
    {
        $array         = json_decode($json, true);
        $jsonLastError = json_last_error();

        if( JSON_ERROR_NONE	!== $jsonLastError ){
            throw new Exception("Not a valid json format");
        }

        $sessionKey                    = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_KEY, "");
        $sessionLifetime               = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_LIFETIME, "");
        $sessionStartTime              = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_START_TIME, "");
        $removeSessionStoredRolesArray = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_REMOVE_SESSION_STORED_ROLES_TO_REMOVE, []);

        $vo = new SingleSessionKeyLifetimeVO();
        $vo->setSessionKey($sessionKey);
        $vo->setSessionLifetime($sessionLifetime);
        $vo->setSessionStartTimestamp($sessionStartTime);
        $vo->setSessionStoredRolesToRemove($removeSessionStoredRolesArray);

        return $vo;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isSessionAllowedToLive(): bool
    {
        $now             = new DateTime();
        $maxLifeDateTime = $this->getSessionEndDateTime();

        if( $now > $maxLifeDateTime ){
            return false;
        }

        return true;
    }
}