<?php

/*
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 * @UniqueEntity(fields={"username", "email"}, message="There is already an account with this username and email")
 */
class User implements UserInterface, EntityInterface {

    const PASSWORD_FIELD   = 'password';
    const USERNAME_FIELD   = "username";
    const EMAIL_FIELD      = "email";
    const ROLE_SUPER_ADMIN = "ROLE_SUPER_ADMIN";

    const ROLE_PERMISSION_SEE_LOCKED_RESOURCES = "ROLE_PERMISSION_SEE_LOCKED_RESOURCES";

    const DEMO_LOGIN    = "admin";
    const DEMO_PASSWORD = "admin";

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
     * @var string $emailCanonical
     * @ORM\Column(type="string", length=100, name="email_canonical")
     */
    private string $emailCanonical = "";

    /**
     * @var string $username
     * @ORM\Column(type="string", length=100)
     */
    private string $username = "";

    /**
     * @var string $usernameCanonical
     * @ORM\Column(type="string", length=100, name="username_canonical")
     */
    private string $usernameCanonical = "";

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
     * @var ?DateTime $lastLogin
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $lastLogin;

    // these fields are only used to transfer data - not being saved directly in DB

    /**
     * @var string $passwordRepeat
     */
    private string $passwordRepeat;

    /**
     * @var string $lockPasswordRepeat
     */
    private string $lockPasswordRepeat;

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
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
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
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime|null $lastLogin
     */
    public function setLastLogin(?DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return string
     */
    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    /**
     * @param string $emailCanonical
     */
    public function setEmailCanonical(string $emailCanonical): void
    {
        $this->emailCanonical = $emailCanonical;
    }

    /**
     * @return string
     */
    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    /**
     * @param string $usernameCanonical
     */
    public function setUsernameCanonical(string $usernameCanonical): void
    {
        $this->usernameCanonical = $usernameCanonical;
    }

    // these fields are only used to transfer data - not being saved directly in DB

    /**
     * @return string
     */
    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    /**
     * @return string
     */
    public function getLockPasswordRepeat(): string
    {
        return $this->lockPasswordRepeat;
    }

    /**
     * @param string $lockPasswordRepeat
     */
    public function setLockPasswordRepeat(string $lockPasswordRepeat): void
    {
        $this->lockPasswordRepeat = $lockPasswordRepeat;
    }

    /**
     * @param string|null $salt
     */
    public function setSalt(?string $salt): void
    {
        $this->salt = $salt;
    }

}
