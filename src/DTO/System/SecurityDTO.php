<?php


namespace App\DTO\System;

use App\DTO\AbstractDTO;

/**
 * Class SecurityDTO
 * @package App\DTO
 */
class SecurityDTO extends AbstractDTO {

    /**
     * @var string $plain_password
     */
    private $plain_password = "";

    /**
     * @var string $hashed_password
     */
    private $hashed_password = "";

    /**
     * @var string $salt
     */
    private $salt = null;

    /**
     * @return string
     */
    public function getPlainPassword(): string {
        return $this->plain_password;
    }

    /**
     * @param string $plain_password
     */
    public function setPlainPassword(string $plain_password): void {
        $this->plain_password = $plain_password;
    }

    /**
     * @return string
     */
    public function getHashedPassword(): string {
        return $this->hashed_password;
    }

    /**
     * @param string $hashed_password
     */
    public function setHashedPassword(string $hashed_password): void {
        $this->hashed_password = $hashed_password;
    }

    /**
     * @return string
     */
    public function getSalt(): ?string {
        return $this->salt;
    }

    /**
     * @param string $salt
     */
    public function setSalt(?string $salt): void {
        $this->salt = $salt;
    }

}