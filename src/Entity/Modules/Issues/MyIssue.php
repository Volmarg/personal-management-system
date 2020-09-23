<?php

namespace App\Entity\Modules\Issues;

use App\Controller\Modules\ModulesController;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\Modules\ModuleMainEntityInterface;
use App\Entity\Interfaces\Relational\RelatesToMyTodoInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use App\Entity\Modules\Todo\MyTodo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Issues\MyIssueRepository")
 */
class MyIssue implements SoftDeletableEntityInterface, EntityInterface, RelatesToMyTodoInterface, ModuleMainEntityInterface
{

    const FIELD_NAME_DELETED  = "deleted";
    const FIELD_NAME_RESOLVED = "resolved";

    /**
     * @var int $id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var bool $deleted
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * @var bool $resolved
     * @ORM\Column(type="boolean")
     */
    private $resolved = false;

    /**
     * @var string $name
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string $information
     * @ORM\Column(type="text")
     */
    private $information;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Issues\MyIssueContact", mappedBy="myIssue")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $issueContact;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Issues\MyIssueProgress", mappedBy="myIssue")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $issueProgress;

    /**
     * @var bool $showOnDashboard
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $showOnDashboard = true;

    /**
     * @ORM\OneToOne(targetEntity=MyTodo::class, cascade={"persist", "remove"})
     */
    private $todo;

    public function __construct()
    {
        $this->issueContact  = new ArrayCollection();
        $this->issueProgress = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isShowOnDashboard(): bool {
        return $this->showOnDashboard;
    }

    /**
     * @param bool $showOnDashboard
     */
    public function setShowOnDashboard(bool $showOnDashboard): void {
        $this->showOnDashboard = $showOnDashboard;
    }

    /**
     * @return string
     * Info: despite the fact that we do not allow null in DB we must allow it here because form data mapper has bug (CORE)
     */
    public function getInformation(): ?string {
        return $this->information;
    }

    /**
     * @param string $information
     */
    public function setInformation(string $information): void {
        $this->information = $information;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     * Info: despite the fact that we do not allow null in DB we must allow it here because form data mapper has bug (CORE)
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }
    /**
     * @return bool
     */
    public function isDeleted(): bool {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void {
        $this->deleted = $deleted;
    }

    /**
     * @return bool
     */
    public function isResolved(): bool {
        return $this->resolved;
    }

    /**
     * @param bool $resolved
     */
    public function setResolved(bool $resolved): void {
        $this->resolved = $resolved;
    }

    /**
     * @param bool $include_deleted
     * @return Collection|MyIssueContact[]
     */
    public function getIssueContact(bool $include_deleted = false): Collection
    {
        foreach($this->issueContact as $index => $issue_contact ){
            if(
                    !$include_deleted
                &&  $issue_contact->isDeleted()
            ){
                unset($this->issueContact[$index]);
            }
        }

        return $this->issueContact;
    }

    public function addIssueContact(MyIssueContact $issueContact): self
    {
        if (!$this->issueContact->contains($issueContact)) {
            $this->issueContact[] = $issueContact;
            $issueContact->setIssue($this);
        }

        return $this;
    }

    public function removeIssueContact(MyIssueContact $issueContact): self
    {
        if ($this->issueContact->contains($issueContact)) {
            $this->issueContact->removeElement($issueContact);
            // set the owning side to null (unless already changed)
            if ($issueContact->getIssue() === $this) {
                $issueContact->setIssue(null);
            }
        }

        return $this;
    }

    /**
     * @param bool $include_deleted
     * @return Collection|MyIssueProgress[]
     */
    public function getIssueProgress(bool $include_deleted = false): Collection
    {
        foreach($this->issueProgress as $index => $issue_progress ){
            if(
                    !$include_deleted
                &&  $issue_progress->isDeleted()
            ){
                unset($this->issueProgress[$index]);
            }
        }

        return $this->issueProgress;
    }

    public function addIssueProgress(MyIssueProgress $issueProgress): self
    {
        if (!$this->issueProgress->contains($issueProgress)) {
            $this->issueProgress[] = $issueProgress;
            $issueProgress->setIssue($this);
        }

        return $this;
    }

    public function removeIssueProgress(MyIssueProgress $issueProgress): self
    {
        if ($this->issueProgress->contains($issueProgress)) {
            $this->issueProgress->removeElement($issueProgress);
            // set the owning side to null (unless already changed)
            if ($issueProgress->getIssue() === $this) {
                $issueProgress->setIssue(null);
            }
        }

        return $this;
    }

    public function getTodo(): ?MyTodo
    {
        return $this->todo;
    }

    public function setTodo(?MyTodo $todo): RelatesToMyTodoInterface
    {
        $this->todo = $todo;

        return $this;
    }

    public function getRelatedModuleName(): string {
        return ModulesController::MODULE_NAME_ISSUES;
    }
}
