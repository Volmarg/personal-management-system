<?php


namespace App\Services\System;


use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class SecurityService {

    /**
     * @var PasswordHasherFactoryInterface $encoderFactory
     */
    private $encoderFactory;

    /**
     * SecurityController constructor.
     *
     * @param PasswordHasherFactoryInterface $passwordHasherFactory
     */
    public function __construct(PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->encoderFactory = $passwordHasherFactory;
    }

    /**
     * @param User $user
     * @param string $userPassword
     * @param string $usedPassword
     * @return bool
     */
    public function isPasswordValid(User $user, string $userPassword, string $usedPassword): bool
    {
        $hasher = $this->encoderFactory->getPasswordHasher($user);

        return $hasher->verify($userPassword, $usedPassword);
    }

}