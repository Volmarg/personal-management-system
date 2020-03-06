<?php


namespace App\Controller\System;


use App\DTO\SecurityDTO;
use App\Entity\User;
use Exception;
use FOS\UserBundle\Util\PasswordUpdater;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

class SecurityController {

    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param string $plain_password
     * @return SecurityDTO|null
     * @throws Exception
     * @see PasswordUpdater::hashPassword() - taken and turned to reusable logic
     */
    public function hashPassword(string $plain_password): ?SecurityDTO
    {
        $user = new User();

        if (0 === strlen($plain_password)) {
            return null;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if ($encoder instanceof BCryptPasswordEncoder || $encoder instanceof SelfSaltingEncoderInterface) {
            $salt = null;
        } else {
            $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        }

        $hashed_password = $encoder->encodePassword($plain_password, $user->getSalt());

        $security_dto = new SecurityDTO();
        $security_dto->setSalt($salt);
        $security_dto->setPlainPassword($plain_password);
        $security_dto->setHashedPassword($hashed_password);

        return $security_dto;
    }

}