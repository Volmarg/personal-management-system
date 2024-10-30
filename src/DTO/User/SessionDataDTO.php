<?php

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use Exception;

class SessionDataDTO extends AbstractDTO
{
    public const KEY_USER_ID = "userId";
    public const KEY_IS_SYSTEM_LOCKED = "isSystemLocked";

    /**
     * @var array
     */
    private array $dataBag = [];

    /**
     * @return array
     */
    public function getDataBag(): array
    {
        return $this->dataBag;
    }

    /**
     * @param array $dataBag
     */
    public function setDataBag(array $dataBag): void
    {
        $this->dataBag = $dataBag;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function setBagValue(string $key, $value): void
    {
        $this->dataBag[$key] = $value;
    }

    /**
     * @return bool
     */
    public function isSystemLocked(): bool
    {
        return $this->dataBag[self::KEY_IS_SYSTEM_LOCKED] ?? true;
    }

    /**
     * @return int
     *
     * @throws Exception
     */
    public function userId(): int
    {
        $userId = $this->dataBag[self::KEY_USER_ID] ?? null;
        if (!$userId) {
            throw new Exception("User id is not set");
        }

        return $userId;
    }

}