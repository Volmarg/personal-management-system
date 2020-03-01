<?php

/*
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace App\Entity;

use App\Controller\AppController;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

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

}
