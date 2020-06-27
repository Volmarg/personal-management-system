<?php

namespace App\VO\Session;

use App\VO\AbstractVO;
use Exception;

class AllSessionsKeysLifetimesVO extends AbstractVO {

    /**
     * @var SingleSessionKeyLifetimeVO[] $single_sessions_keys_lifetimes
     */
    private $single_sessions_keys_lifetimes = [];

    /**
     * Returns array of all single session keys lifetimes that should be active right now
     * @return SingleSessionKeyLifetimeVO[]
     */
    public function getSingleSessionsKeysLifetimes(): array
    {
        return $this->single_sessions_keys_lifetimes;
    }

    /**
     * Set array of all single session key lifetimes
     * @param SingleSessionKeyLifetimeVO[] $single_sessions_keys_lifetimes
     */
    public function setSingleSessionsKeysLifetimes(array $single_sessions_keys_lifetimes): void
    {
        $this->single_sessions_keys_lifetimes = $single_sessions_keys_lifetimes;
    }

    /**
     * This function will remove the expired session key VO, so there is no need to build
     * additional logic to recreate array without expired sessions
     *
     * Info: this does not set changes in session, only in the array of sessions_lifetimes
     * Will return expired session keys
     * @param array $keys_to_compare
     *         if provided then additionally invalidates sessions lifetimes which are not in the array
     *         with this it's possible to invalidate lifetimes when somewhere else user/system removed something from session
     * @throws Exception
     */
    public function unsetExpiredSingleSessionsKeysLifetimes(array $keys_to_compare = []): void
    {
        foreach($this->single_sessions_keys_lifetimes as $index => $single_session_key_lifetime ){
            $session_key = $single_session_key_lifetime->getSessionKey();

            if( !$single_session_key_lifetime->isSessionAllowedToLive() ){
                unset($this->single_sessions_keys_lifetimes[$index]);
            }elseif(
                    !empty($keys_to_compare)
                &&  !in_array($session_key, $keys_to_compare)
            ){
                unset($this->single_sessions_keys_lifetimes[$index]);
            }
        }

        // reset array index after unset
        $this->single_sessions_keys_lifetimes = array_keys($this->single_sessions_keys_lifetimes);
    }

    /**
     * Returns all non expired session keys (have valid lifetime by now)
     * @return SingleSessionKeyLifetimeVO[]
     * @throws Exception
     */
    public function getNonExpiredSessionsKeysLifetimes(): array
    {
        $non_expired_single_session_key_lifetimes_vo = [];

        foreach($this->single_sessions_keys_lifetimes as $index => $single_session_key_lifetime ){
            if( $single_session_key_lifetime->isSessionAllowedToLive() ){
                $non_expired_single_session_key_lifetimes_vo[] = $single_session_key_lifetime;
            }
        }

        return $non_expired_single_session_key_lifetimes_vo;
    }

    /**
     * Returns all expired session keys (have invalid lifetime by now)
     * @return SingleSessionKeyLifetimeVO[]
     * @throws Exception
     */
    public function getExpiredSessionsKeysLifetimes(): array
    {
        $expired_single_session_key_lifetimes_vo = [];

        foreach($this->single_sessions_keys_lifetimes as $index => $single_session_key_lifetime ){
            if( !$single_session_key_lifetime->isSessionAllowedToLive() ){
                $expired_single_session_key_lifetimes_vo[] = $single_session_key_lifetime;
            }
        }

        return $expired_single_session_key_lifetimes_vo;
    }

    /**
     * Will start counting lifetime again for given moment as start time
     * @throws Exception
     */
    public function refreshSessionsKeysLifetimes(): void
    {
        foreach($this->single_sessions_keys_lifetimes as $single_sessions_keys_lifetime ){
            $single_sessions_keys_lifetime->resetSessionStartTime();
        }
    }

    /**
     * Will add new lifetime key if no such was found, otherwise it will be replace
     * @param SingleSessionKeyLifetimeVO $added_session_key_lifetime
     */
    public function addSessionLifetimeVO(SingleSessionKeyLifetimeVO $added_session_key_lifetime): void
    {
        $added_session_lifetime_key = $added_session_key_lifetime->getSessionKey();

        foreach($this->single_sessions_keys_lifetimes as $index => $existing_session_key_lifetime ) {
            $existing_session_key = $existing_session_key_lifetime->getSessionKey();

            if( $existing_session_key === $added_session_lifetime_key ){
                $this->single_sessions_keys_lifetimes[$index] = $added_session_key_lifetime;
                return;
            }
        }

        $this->single_sessions_keys_lifetimes[] = $added_session_key_lifetime;
    }

    /**
     * Returns information if there is an expirable session with given key
     * @param string $searched_key
     * @return bool
     */
    public function hasSingleSessionKeyLifetime(string $searched_key): bool
    {
        foreach( $this->single_sessions_keys_lifetimes as $single_sessions_keys_lifetime ){
            $session_key = $single_sessions_keys_lifetime->getSessionKey();

            if( $session_key == $searched_key ){
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $json
     * @return AllSessionsKeysLifetimesVO
     * @throws Exception
     */
    public static function fromJson(string $json): AllSessionsKeysLifetimesVO
    {
        $single_sessions_keys_lifetimes_arrays = json_decode($json, true);
        $all_sessions_keys_lifetimes           = new AllSessionsKeysLifetimesVO();

        foreach( $single_sessions_keys_lifetimes_arrays as $single_session_key_lifetime_array ){
            $single_session_key_lifetime_json = json_encode($single_session_key_lifetime_array);
            $single_session_key_lifetime      = SingleSessionKeyLifetimeVO::fromJson($single_session_key_lifetime_json);
            $all_sessions_keys_lifetimes->addSessionLifetimeVO($single_session_key_lifetime);
        }

        return $all_sessions_keys_lifetimes;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toJson(): string
    {
        $all_sessions_keys_lifetimes_arrays = [];

        foreach($this->single_sessions_keys_lifetimes as $single_session_key_lifetime ){
            $single_session_key_lifetime_array    = $single_session_key_lifetime->toArray();
            $all_sessions_keys_lifetimes_arrays[] = $single_session_key_lifetime_array;
        }

        $all_sessions_keys_lifetimes_json = json_encode($all_sessions_keys_lifetimes_arrays);
        return $all_sessions_keys_lifetimes_json;
    }
}