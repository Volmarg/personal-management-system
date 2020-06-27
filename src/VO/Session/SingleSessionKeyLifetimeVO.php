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
     * @var string $session_key
     */
    private $session_key = '';

    /**
     * @var int $session_start_timestamp
     */
    private $session_start_timestamp = 0;

    /**
     * @var int $session_lifetime
     */
    private $session_lifetime;

    /**
     * @var DateTime $session_end_datetime
     */
    private $session_end_datetime;

    /**
     * @var array $session_stored_roles_to_remove
     */
    private $session_stored_roles_to_remove = [];

    /**
     * @return string
     */
    public function getSessionKey(): string {
        return $this->session_key;
    }

    /**
     * @param string $session_key
     */
    public function setSessionKey(string $session_key): void {
        $this->session_key = $session_key;
    }

    /**
     * @return int
     */
    public function getSessionStartTimestamp(): int {
        return $this->session_start_timestamp;
    }

    /**
     * @param int $session_start_timestamp
     * @throws Exception
     */
    public function setSessionStartTimestamp(int $session_start_timestamp): void {
        $max_lifetime_timestamp = $session_start_timestamp + $this->session_lifetime;
        $max_life_date_time     = new DateTime();
        $max_life_date_time->setTimestamp($max_lifetime_timestamp);

        $this->session_start_timestamp = $session_start_timestamp;
        $this->session_end_datetime    = $max_life_date_time;
    }

    /**
     * @throws Exception
     */
    public function resetSessionStartTime(): void
    {
        $this->session_start_timestamp = (new DateTime())->getTimestamp();
    }

    /**
     * @return mixed
     */
    public function getSessionLifetime() {
        return $this->session_lifetime;
    }

    /**
     * @param mixed $session_lifetime
     */
    public function setSessionLifetime($session_lifetime): void {
        $this->session_lifetime = $session_lifetime;
    }

    /**
     * @throws Exception
     */
    public function getSessionEndDateTime(): DateTime {
        return $this->session_end_datetime;
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
        return $this->session_stored_roles_to_remove;
    }

    /**
     * @param array $session_stored_roles_to_remove
     */
    public function setSessionStoredRolesToRemove(array $session_stored_roles_to_remove): void {
        $this->session_stored_roles_to_remove = $session_stored_roles_to_remove;
    }

    /**
     * @return bool
     */
    public function doRemoveSessionStoredRolesInsteadOfSessionKey(): bool
    {
        return !empty($this->session_stored_roles_to_remove);
    }

    /**
     * @param string $json
     * @return SingleSessionKeyLifetimeVO
     * @throws Exception
     */
    public static function fromJson(string $json): SingleSessionKeyLifetimeVO
    {
        $array           = json_decode($json, true);
        $json_last_error = json_last_error();

        if( JSON_ERROR_NONE	!== $json_last_error ){
            throw new Exception("Not a valid json format");
        }

        $session_key                       = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_KEY, "");
        $session_lifetime                  = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_LIFETIME, "");
        $session_start_time                = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_START_TIME, "");
        $remove_session_stored_roles_array = AbstractVO::checkAndGetKey($array, self::KEY_SESSION_REMOVE_SESSION_STORED_ROLES_TO_REMOVE, []);

        $vo = new SingleSessionKeyLifetimeVO();
        $vo->setSessionKey($session_key);
        $vo->setSessionLifetime($session_lifetime);
        $vo->setSessionStartTimestamp($session_start_time);
        $vo->setSessionStoredRolesToRemove($remove_session_stored_roles_array);

        return $vo;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isSessionAllowedToLive(): bool
    {
        $now                = new DateTime();
        $max_life_date_time = $this->getSessionEndDateTime();

        if( $now > $max_life_date_time ){
            return false;
        }

        return true;
    }
}