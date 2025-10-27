<?php


namespace App\Services\System;


use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class SecurityService {

    /**
     * @var EncoderFactoryInterface $encoderFactory
     */
    private $encoderFactory;

    /**
     * SecurityController constructor.
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory  = $encoderFactory;
    }

    /**
     * @param User $user
     * @param string $userPassword
     * @param string $usedPassword
     * @param string|null $saltForUsedPassword
     * @return bool
     */
    public function isPasswordValid(User $user, string $userPassword, string $usedPassword, ?string $saltForUsedPassword = null): bool
    {
        $encoder         = $this->encoderFactory->getEncoder($user);
        $isPasswordValid = $encoder->isPasswordValid($userPassword, $usedPassword, $saltForUsedPassword);
        return $isPasswordValid;
    }

}