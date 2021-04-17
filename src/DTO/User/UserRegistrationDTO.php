<?php

namespace App\DTO\User;

use App\DTO\Interfaces\ValidableDtoInterface;

/**
 * This dto represents the data passed in the registration form
 *
 * Class UserRegistrationDTO
 * @package App\DTO\User
 */
class UserRegistrationDTO implements ValidableDtoInterface
{
    const FIELD_USERNAME             = "username";
    const FIELD_EMAIL                = "email";
    const FIELD_PASSWORD             = "password";
    const FIELD_PASSWORD_REPEAT      = "passwordRepeat";
    const FIELD_LOCK_PASSWORD        = "lockPassword";
    const FIELD_LOCK_PASSWORD_REPEAT = "lockPasswordRepeat";

    /**
     * @var string $username
     */
    private string $username = "";

    /**
     * @var string $username
     */
    private string $email = "";

    /**
     * @var string $username
     */
    private string $password = "";

    /**
     * @var string $username
     */
    private string $passwordRepeat = "";

    /**
     * @var string $username
     */
    private string $lockPassword = "";

    /**
     * @var string $username
     */
    private string $lockPasswordRepeat = "";

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    /**
     * @return string
     */
    public function getLockPassword(): string
    {
        return $this->lockPassword;
    }

    /**
     * @param string $lockPassword
     */
    public function setLockPassword(string $lockPassword): void
    {
        $this->lockPassword = $lockPassword;
    }

    /**
     * @return string
     */
    public function getLockPasswordRepeat(): string
    {
        return $this->lockPasswordRepeat;
    }

    /**
     * @param string $lockPasswordRepeat
     */
    public function setLockPasswordRepeat(string $lockPasswordRepeat): void
    {
        $this->lockPasswordRepeat = $lockPasswordRepeat;
    }

}