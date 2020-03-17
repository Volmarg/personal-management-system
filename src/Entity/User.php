<?php

/*
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 */
class User extends BaseUser {

    const PASSWORD_FIELD = 'password';
    const ROLE_ADMIN     = "ROLE_ADMIN";

    const ROLE_PERMISSION_SEE_LOCKED_RESOURCES = "ROLE_PERMISSION_SEE_LOCKED_RESOURCES";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct() {
        parent::__construct();
        // your own logic
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $nickname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lockPassword;

    /**
     * @param UserInterface $base_user
     * @return User
     */
    public static function createFromBaseUser(UserInterface $base_user): User
    {
        $user = new User();

        $email              = $base_user->getEmail();
        $email_canonical    = $base_user->getEmailCanonical();
        $username           = $base_user->getUsername();
        $username_canonical = $base_user->getUsernameCanonical();
        $enabled            = $base_user->isEnabled();
        $salt               = $base_user->getSalt();
        $roles              = $base_user->getRoles();
        $password           = $base_user->getPassword();

        $user->setEmail($email);
        $user->setEmailCanonical($email_canonical);
        $user->setUsername($username);
        $user->setUsernameCanonical($username_canonical);
        $user->setEnabled($enabled);
        $user->setSalt($salt);
        $user->setRoles($roles);
        $user->setPassword($password);

        return $user;
    }

    /**
     * @return string | null
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar(?string $avatar): void {
        $this->avatar = $avatar;
    }

    /**
     * @return string | null
     */
    public function getNickname() {
        return $this->nickname;
    }

    /**
     * @param mixed $nickname
     */
    public function setNickname(?string $nickname): void {
        $this->nickname = $nickname;
    }

    /**
     * @return mixed
     */
    public function getLockPassword() {
        return $this->lockPassword;
    }

    /**
     * @param mixed $lockPassword
     */
    public function setLockPassword($lockPassword): void {
        $this->lockPassword = $lockPassword;
    }

    public function hasLockPassword(){
        if( empty($this->lockPassword) ){
            return false;
        }
        return true;
    }
}
