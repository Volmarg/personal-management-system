<?php

namespace App\Services\Security;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class PasswordHashingService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordEncoder
    ) {
    }

    /**
     * Will encode plain password for standard login user interface
     *
     * @param string $plainPassword
     *
     * @return string
     */
    public function encode(string $plainPassword): string
    {
        // it's required to use even blank user entity to fetch the encoder from it
        return $this->userPasswordEncoder->hashPassword(new User(), $plainPassword);
    }

}