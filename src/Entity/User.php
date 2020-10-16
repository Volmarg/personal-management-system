<?php

/*
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 * @UniqueEntity(fields={"username, email"}, message="There is already an account with this username and email")
 */
class User implements UserInterface {

    const PASSWORD_FIELD = 'password';
    const ROLE_ADMIN     = "ROLE_ADMIN";

    const ROLE_PERMISSION_SEE_LOCKED_RESOURCES = "ROLE_PERMISSION_SEE_LOCKED_RESOURCES";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $nickname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lockPassword;

    /**
     * @ORM\Column(type="serialized_json")
     */
    private array $roles = [];

    /**
     * @var string $email
     * @ORM\Column(type="string", length=100)
     */
    private string $email = "";

    /**
     * @var string $email
     * @ORM\Column(type="string", length=100)
     */
    private string $username = "";

    /**
     * @var bool $enabled
     * @ORM\Column(type="boolean")
     */
    private $enabled = 1;

    /**
     * @var string|null $salt
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $salt = "";

    /**
     * @var DateTime $lastLogin
     * @ORM\Column(type="datetime")
     */
    private DateTime $lastLogin;

# todo: migration to move user data to new table and remove old
# todo: update demo data
# todo: command to generate user
# todo: add register form instead of using command,
# todo: command should be able only to reset password

    public function __construct() {
        $this->lastLogin = new DateTime();
    }

    /**
     * @return string | null
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * @param string|null $avatar
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return DateTime
     */
    public function getLastLogin(): DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime $lastLogin
     */
    public function setLastLogin(DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

}
