<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    const ENABLED               = true;
    const SALT                  = NULL;
    const LAST_LOGIN            = NULL;
    const CONFIRMATION_TOKEN    = NULL;
    const PASSWORD_REQUESTED_AT = NULL;
    const AVATAR                = NULL;
    const NICKNAME              = NULL;
    const PASSWORD              = '$2y$13$.VnnN5tJ8evchXidKXZnZePceiQ1FFzr/9SLg8DNGyeKpbnqBelDW'; #admin
    const ROLES                 = 'ROLE_SUPER_ADMIN';
    const USERNAME              = 'admin';


    public function load(ObjectManager $manager)
    {
        $appUser  = new User();
        $appUser->setUsername(static::USERNAME);
        $appUser->setUsernameCanonical(static::USERNAME);
        $appUser->setEmail(static::USERNAME);
        $appUser->setEmailCanonical(static::USERNAME);
        $appUser->setEnabled(static::ENABLED);
        $appUser->setSalt(static::SALT);
        $appUser->setPassword(static::PASSWORD);
        $appUser->setLockPassword(static::PASSWORD);
        $appUser->setLastLogin(static::LAST_LOGIN);
        $appUser->setRoles([static::ROLES]);
        $appUser->setAvatar(static::AVATAR);
        $appUser->setNickname(static::NICKNAME);

        $manager->persist($appUser);
        $manager->flush();
    }
}
