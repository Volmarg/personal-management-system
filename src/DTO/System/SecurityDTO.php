<?php


namespace App\DTO\System;

use App\DTO\AbstractDTO;

/**
 * Class SecurityDTO
 * @package App\DTO
 */
class SecurityDTO extends AbstractDTO {

    /**
     * @var string $plainPassword
     */
    private $plainPassword = "";

    /**
     * @var string $hashedPassword
     */
    private $hashedPassword = "";

    /**
     * @var string $salt
     */
    private $salt = null;

    /**
     * @return string
     */
    public function getPlainPassword(): string {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return string
     */
    public function getHashedPassword(): string {
        return $this->hashedPassword;
    }

    /**
     * @param string $hashedPassword
     */
    public function setHashedPassword(string $hashedPassword): void {
        $this->hashedPassword = $hashedPassword;
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