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
    private Controllers $controllers;

    public function __construct(Controllers $controllers)
    {
        $this->controllers = $controllers;
    }

    /**
     * @Route("/api/system/validate-password", name="system_validate_password")
     * @param User $user
     * @param string $userPassword
     * @param string $usedPassword
     * @param string|null $saltForUsedPassword
     * @return bool
     */
    public function isPasswordValid(User $user, string $userPassword, string $usedPassword, ?string $saltForUsedPassword = null): bool
    {
        $isPasswordValid = $this->controllers->getSecurityController()->isPasswordValid($user, $userPassword, $usedPassword, $saltForUsedPassword);
        return $isPasswordValid;
    }

}