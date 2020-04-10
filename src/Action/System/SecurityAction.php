<?php


namespace App\Action\System;


use App\Controller\Core\Controllers;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityAction extends AbstractController {

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Controllers $controllers) {
        $this->controllers = $controllers;
    }

    /**
     * @Route("/api/system/validate-password", name="system_validate_password")
     * @param User $user
     * @param string $user_password
     * @param string $used_password
     * @param string|null $salt_for_used_password
     * @return bool
     */
    public function isPasswordValid(User $user, string $user_password, string $used_password, ?string $salt_for_used_password = null): bool
    {
        $is_password_valid = $this->controllers->getSecurityController()->isPasswordValid($user, $user_password, $used_password, $salt_for_used_password);
        return $is_password_valid;
    }

}