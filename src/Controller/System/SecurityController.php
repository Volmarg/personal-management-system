<?php


namespace App\Controller\System;


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
     * @var UserController $user_controller
     */
    private UserController $user_controller;

    /**
     * SecurityController constructor.
     * @param EncoderFactoryInterface $encoderFactory
     * @param UserController $user_controller
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, UserController $user_controller)
    {
        $this->encoderFactory  = $encoderFactory;
        $this->user_controller = $user_controller;
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
        $all_registered_users = $this->user_controller->getAllUsers();
        $count_of_users       = count($all_registered_users);

        if( $count_of_users > 0 ){
            return false;
        }

        return true;
    }

}