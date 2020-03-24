<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

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
        $app_user  = new User();
        $app_user->setUsername(static::USERNAME);
        $app_user->setUsernameCanonical(static::USERNAME);
        $app_user->setEmail(static::USERNAME);
        $app_user->setEmailCanonical(static::USERNAME);
        $app_user->setEnabled(static::ENABLED);
        $app_user->setSalt(static::SALT);
        $app_user->setPassword(static::PASSWORD);
        $app_user->setLockPassword(static::PASSWORD);
        $app_user->setLastLogin(static::LAST_LOGIN);
        $app_user->setConfirmationToken(static::CONFIRMATION_TOKEN);
        $app_user->setPasswordRequestedAt(static::PASSWORD_REQUESTED_AT);
        $app_user->setRoles([static::ROLES]);
        $app_user->setAvatar(static::AVATAR);
        $app_user->setNickname(static::NICKNAME);

        $manager->persist($app_user);
        $manager->flush();
    }
}
