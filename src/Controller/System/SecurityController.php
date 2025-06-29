<?php


namespace App\Controller\System;


use App\DTO\System\SecurityDTO;
use App\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

class SecurityController {

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
     * @param string $plainPassword
     * @return SecurityDTO|null
     * @throws Exception
     * @see PasswordUpdater::hashPassword() - taken and turned to reusable logic
     */
    public function hashPassword(string $plainPassword): ?SecurityDTO
    {
        $user = new User();

        if (0 === strlen($plainPassword)) {
            return null;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if ($encoder instanceof BCryptPasswordEncoder || $encoder instanceof SelfSaltingEncoderInterface) {
            $salt = null;
        } else {
            $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        }

        $hashedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());

        $securityDto = new SecurityDTO();
        $securityDto->setSalt($salt);
        $securityDto->setPlainPassword($plainPassword);
        $securityDto->setHashedPassword($hashedPassword);

        return $securityDto;
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