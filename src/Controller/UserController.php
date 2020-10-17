<?php


namespace App\Controller;


use App\Controller\Core\Application;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

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

    /**
     * Will return all users from database
     * @return User[]
     */
    public function getAllUsers()
    {
        return $this->app->repositories->userRepository->getAllUsers();
    }

    /**
     * Will return one user for given username
     * or if no user was found then null is being returned
     * @param string $username
     * @return User|null
     */
    public function findOneByName(string $username): ?User
    {
        return $this->app->repositories->userRepository->findOneByName($username);
    }

    /**
     * Will save given user in database
     *
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveUser(User $user): void
    {
        $this->app->repositories->userRepository->saveUser($user);
    }
}