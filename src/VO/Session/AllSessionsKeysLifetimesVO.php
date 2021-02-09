<?php

namespace App\VO\Session;

use App\VO\AbstractVO;
use Exception;

class AllSessionsKeysLifetimesVO extends AbstractVO {

    /**
     * @var SingleSessionKeyLifetimeVO[] $singleSessionsKeysLifetimes
     */
    private $singleSessionsKeysLifetimes = [];

    /**
     * Returns array of all single session keys lifetimes that should be active right now
     * @return SingleSessionKeyLifetimeVO[]
     */
    public function getSingleSessionsKeysLifetimes(): array
    {
        return $this->singleSessionsKeysLifetimes;
    }

    /**
     * Set array of all single session key lifetimes
     * @param SingleSessionKeyLifetimeVO[] $singleSessionsKeysLifetimes
     */
    public function setSingleSessionsKeysLifetimes(array $singleSessionsKeysLifetimes): void
    {
        $this->singleSessionsKeysLifetimes = $singleSessionsKeysLifetimes;
    }

    /**
     * This function will remove the expired session key VO, so there is no need to build
     * additional logic to recreate array without expired sessions
     *
     * Info: this does not set changes in session, only in the array of sessions_lifetimes
     * Will return expired session keys
     * @param array $keysToCompare
     *         if provided then additionally invalidates sessions lifetimes which are not in the array
     *         with this it's possible to invalidate lifetimes when somewhere else user/system removed something from session
     * @throws Exception
     */
    public function unsetExpiredSingleSessionsKeysLifetimes(array $keysToCompare = []): void
    {
        foreach($this->singleSessionsKeysLifetimes as $index => $singleSessionKeyLifetime ){
            $sessionKey = $singleSessionKeyLifetime->getSessionKey();

            if( !$singleSessionKeyLifetime->isSessionAllowedToLive() ){
                unset($this->singleSessionsKeysLifetimes[$index]);
            }elseif(
                    !empty($keysToCompare)
                &&  !in_array($sessionKey, $keysToCompare)
            ){
                unset($this->singleSessionsKeysLifetimes[$index]);
            }
        }

        // reset array index after unset
        $this->singleSessionsKeysLifetimes = array_keys($this->singleSessionsKeysLifetimes);
    }

    /**
     * Returns all non expired session keys (have valid lifetime by now)
     * @return SingleSessionKeyLifetimeVO[]
     * @throws Exception
     */
    public function getNonExpiredSessionsKeysLifetimes(): array
    {
        $nonExpiredSingleSessionKeyLifetimesVo = [];
        foreach($this->singleSessionsKeysLifetimes as $index => $singleSessionKeyLifetime ){
            if( $singleSessionKeyLifetime->isSessionAllowedToLive() ){
                $nonExpiredSingleSessionKeyLifetimesVo[] = $singleSessionKeyLifetime;
            }
        }

        return $nonExpiredSingleSessionKeyLifetimesVo;
    }

    /**
     * Returns all expired session keys (have invalid lifetime by now)
     * @return SingleSessionKeyLifetimeVO[]
     * @throws Exception
     */
    public function getExpiredSessionsKeysLifetimes(): array
    {
        $expiredSingleSessionKeyLifetimesVo = [];
        foreach($this->singleSessionsKeysLifetimes as $index => $singleSessionKeyLifetime ){
            if( !$singleSessionKeyLifetime->isSessionAllowedToLive() ){
                $expiredSingleSessionKeyLifetimesVo[] = $singleSessionKeyLifetime;
            }
        }

        return $expiredSingleSessionKeyLifetimesVo;
    }

    /**
     * Will start counting lifetime again for given moment as start time
     * @throws Exception
     */
    public function refreshSessionsKeysLifetimes(): void
    {
        foreach($this->singleSessionsKeysLifetimes as $singleSessionsKeysLifetime ){
            $singleSessionsKeysLifetime->resetSessionStartTime();
        }
    }

    /**
     * Will add new lifetime key if no such was found, otherwise it will be replace
     * @param SingleSessionKeyLifetimeVO $addedSessionKeyLifetime
     */
    public function addSessionLifetimeVO(SingleSessionKeyLifetimeVO $addedSessionKeyLifetime): void
    {
        $addedSessionLifetimeKey = $addedSessionKeyLifetime->getSessionKey();
        foreach($this->singleSessionsKeysLifetimes as $index => $existingSessionKeyLifetime ) {
            $existingSessionKey = $existingSessionKeyLifetime->getSessionKey();

            if( $existingSessionKey === $addedSessionLifetimeKey ){
                $this->singleSessionsKeysLifetimes[$index] = $addedSessionKeyLifetime;
                return;
            }
        }

        $this->singleSessionsKeysLifetimes[] = $addedSessionKeyLifetime;
    }

    /**
     * Returns information if there is an expirable session with given key
     * @param string $searchedKey
     * @return bool
     */
    public function hasSingleSessionKeyLifetime(string $searchedKey): bool
    {
        foreach($this->singleSessionsKeysLifetimes as $singleSessionsKeysLifetime ){
            $sessionKey = $singleSessionsKeysLifetime->getSessionKey();

            if( $sessionKey == $searchedKey ){
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
        $singleSessionsKeysLifetimesArrays = json_decode($json, true);
        $allSessionsKeysLifetimes          = new AllSessionsKeysLifetimesVO();

        foreach( $singleSessionsKeysLifetimesArrays as $singleSessionKeyLifetimeArray ){
            $singleSessionKeyLifetimeJson = json_encode($singleSessionKeyLifetimeArray);
            $singleSessionKeyLifetime     = SingleSessionKeyLifetimeVO::fromJson($singleSessionKeyLifetimeJson);
            $allSessionsKeysLifetimes->addSessionLifetimeVO($singleSessionKeyLifetime);
        }

        return $allSessionsKeysLifetimes;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toJson(): string
    {
        $allSessionsKeysLifetimesArrays = [];

        foreach($this->singleSessionsKeysLifetimes as $singleSessionKeyLifetime ){
            $singleSessionKeyLifetimeArray    = $singleSessionKeyLifetime->toArray();
            $allSessionsKeysLifetimesArrays[] = $singleSessionKeyLifetimeArray;
        }

        $allSessionsKeysLifetimesJson = json_encode($allSessionsKeysLifetimesArrays);
        return $allSessionsKeysLifetimesJson;
    }
}