<?php

namespace App\Entity\Modules\Goals;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Goals\MyGoalsSubgoalsRepository")
 * @ORM\Table(name="my_goal_subgoal")
 */
class MyGoalsSubgoals
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
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $completed = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Modules\Goals\MyGoals", inversedBy="myGoal")
     */
    private $myGoal;

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

    public function getDeleted(): ?bool
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

    public function getMyGoal(): ?MyGoals
    {
        return $this->myGoal;
    }

    public function setMyGoal(?MyGoals $myGoal): self
    {
        $this->myGoal = $myGoal;

        return $this;
    }

}
