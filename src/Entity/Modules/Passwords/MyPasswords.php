<?php

namespace App\Entity\Modules\Passwords;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use SpecShaper\EncryptBundle\Annotations\Encrypted;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Passwords\MyPasswordsRepository")
 * @ORM\Table(name="my_password")
 */
class MyPasswords implements SoftDeletableEntityInterface, EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login;

    /**
     * @var string
     * @Encrypted
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Passwords\MyPasswordsGroups")
     */
    private $group;

    public function getId(): ?int {
        return $this->id;
    }

    public function getLogin(): ?string {
        return $this->login;
    }

    public function setLogin(string $login): self {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(?string $password): self {
        $this->password = $password;

        return $this;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): self {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): ?bool {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return MyPasswordsGroups
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @param MyPasswordsGroups $group
     * @return MyPasswords
     */
    public function setGroup($group): self {
        $this->group = $group;

        return $this;
    }


}
