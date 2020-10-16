<?php


namespace App\Controller;


use App\Controller\Core\Application;

class UserController
{
    /**
     * @var Application $app
     */
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getAllUsers()
    {
        return $this->app->repositories->userRepository->getAllUsers();
    }

}