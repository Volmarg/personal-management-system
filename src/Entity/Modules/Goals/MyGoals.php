<?php

namespace App\Entity\Modules\Goals;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// todo: remove  subgoal etc -> add 1:1 to `todo`

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Goals\MyGoalsRepository")
 * @ORM\Table(name="my_goal")
 */
class MyGoals implements SoftDeletableEntityInterface, EntityInterface
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $completed = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Modules\Goals\MyGoalsSubgoals", mappedBy="myGoal")
     */
    private $mySubgoal;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayOnDashboard = 0;

    public function __construct()
    {
        $this->mySubgoal = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * @return Collection|MyGoalsSubgoals[]
     */
    public function getMySubgoal(): Collection
    {
        return $this->mySubgoal;
    }

    public function addMySubgoal(MyGoalsSubgoals $mySubgoal): self
    {
        if (!$this->mySubgoal->contains($mySubgoal)) {
            $this->mySubgoal[] = $mySubgoal;
            $mySubgoal->setMyGoal($this);
        }

        return $this;
    }

    public function removeMySubgoal(MyGoalsSubgoals $mySubgoal): self
    {
        if ($this->mySubgoal->contains($mySubgoal)) {
            $this->mySubgoal->removeElement($mySubgoal);
            // set the owning side to null (unless already changed)
            if ($mySubgoal->getMyGoal() === $this) {
                $mySubgoal->setMyGoal(null);
            }
        }

        return $this;
    }

    public function getDisplayOnDashboard(): ?bool
    {
        return $this->displayOnDashboard;
    }

    public function setDisplayOnDashboard(bool $displayOnDashboard): self
    {
        $this->displayOnDashboard = $displayOnDashboard;

        return $this;
    }
}
