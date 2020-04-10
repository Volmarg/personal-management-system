<?php


namespace App\Controller\System;


use App\DTO\System\SecurityDTO;
use App\Entity\User;
use Exception;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\PasswordUpdater;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

class SecurityController {

    /**
     * @var EncoderFactoryInterface $encoderFactory
     */
    private $encoderFactory;

    /**
     * @var UserManagerInterface $userManager
     */
    private $userManager;

    public function __construct(EncoderFactoryInterface $encoderFactory, UserManagerInterface $userManager)
    {
        $this->encoderFactory = $encoderFactory;
        $this->userManager    = $userManager;
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

    /**
     * @param User $user
     * @param string $user_password
     * @param string $used_password
     * @param string|null $salt_for_used_password
     * @return bool
     */
    public function isPasswordValid(User $user, string $user_password, string $used_password, ?string $salt_for_used_password = null): bool
    {
        $encoder           = $this->encoderFactory->getEncoder($user);
        $is_password_valid = $encoder->isPasswordValid($user_password, $used_password, $salt_for_used_password);
        return $is_password_valid;
    }

}