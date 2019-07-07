<?php

namespace App\Entity\Modules\Goals;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Goals\MyGoalsPaymentsRepository")
 */
class MyGoalsPayments
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
    private $deadline;

    /**
     * @ORM\Column(type="integer")
     */
    private $moneyGoal;

    /**
     * @ORM\Column(type="integer")
     */
    private $moneyCollected;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $collectionStartDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayOnDashboard = 0;

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

    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    public function setDeadline(?string $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getMoneyGoal(): ?int
    {
        return $this->moneyGoal;
    }

    public function setMoneyGoal(int $moneyGoal): self
    {
        $this->moneyGoal = $moneyGoal;

        return $this;
    }

    public function getMoneyCollected(): ?int
    {
        return $this->moneyCollected;
    }

    public function setMoneyCollected(int $moneyCollected): self
    {
        $this->moneyCollected = $moneyCollected;

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

    public function getCollectionStartDate(): ?string
    {
        return $this->collectionStartDate;
    }

    public function setCollectionStartDate(string $collectionStartDate): self
    {
        $this->collectionStartDate = $collectionStartDate;

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
