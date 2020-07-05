<?php

namespace App\Entity\Modules\Goals;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\SoftDeletableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Goals\MyGoalsPaymentsRepository")
 * @ORM\Table(name="my_goal_payment")
 */
class MyGoalsPayments implements SoftDeletableEntityInterface, EntityInterface
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
     * @ORM\Column(type="date", length=255, nullable=true)
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
     * @ORM\Column(type="date", length=255)
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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeadline() {
        return $this->deadline;
    }

    /**
     * @param $deadline
     * @return $this
     * @throws \Exception
     */
    public function setDeadline($deadline) {

        if( is_string($deadline) ){
            $deadline = new \DateTime($deadline);
        }

        $this->deadline = $deadline;

        return $this;
    }

    public function getCollectionStartDate() {
        return $this->collectionStartDate;
    }

    /**
     * @param $collectionStartDate
     * @return $this
     * @throws \Exception
     */
    public function setCollectionStartDate($collectionStartDate) {

        if( is_string($collectionStartDate) ){
            $collectionStartDate = new \DateTime($collectionStartDate);
        }

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
