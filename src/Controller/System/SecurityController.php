<?php


namespace App\Controller\System;


use App\Controller\Core\Env;
use App\Controller\UserController;
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
     * @var UserController $userController
     */
    private UserController $userController;

    /**
     * SecurityController constructor.
     * @param EncoderFactoryInterface $encoderFactory
     * @param UserController $userController
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, UserController $userController)
    {
        $this->encoderFactory  = $encoderFactory;
        $this->userController = $userController;
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

    /**
     * Returns the information if it's allowed to register user in system
     *
     * WARNING!
     *
     * This is the main method which control permission to register showing/hiding registration.
     * The `personal` in the project stands for ONE USER for ONE PROJECT INSTANCE
     *
     * If Your really for some reason want to have more users then set `return true` in this method
     * However the project was never tested with more than one user so You potentially risk loosing some data
     * This should never happen because all the entries are saved globally without user tracking but You just
     * need to be aware of it
     *
     * @return bool
     */
    public function canRegisterUser(): bool
    {
        if( Env::isDemo() ){
            return false;
        }

        $allRegisteredUsers = $this->userController->getAllUsers();
        $countOfUsers       = count($allRegisteredUsers);

        if( $countOfUsers > 0 ){
            return false;
        }

        return true;
    }

}