<?php

namespace App\DTO\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use DateTime;

class IssueCardDTO {

    /**
     * @var MyIssue $issue
     */
    private $issue;

    /**
     * @var array $issueContactsByIcon
     */
    private $issueContactsByIcon = [];

    /**
     * @var int $issueContactsCount
     */
    private $issueContactsCount = 0;

    /**
     * @var int $issueProgressCount
     */
    private $issueProgressCount = 0;

    /**
     * @var DateTime $issueLastContact
     */
    private $issueLastContact = null;

    /**
     * @var DateTime $issueLastProgress
     */
    private $issueLastProgress = null;

    /**
     * @var string[] $waitingTodo
     */
    private $waitingTodo = [];

    /**
     * @return MyIssue
     */
    public function getIssue(): MyIssue {
        return $this->issue;
    }

    /**
     * @param MyIssue $issue
     */
    public function setIssue(MyIssue $issue): void {
        $this->issue = $issue;
    }

    /**
     * @return array
     */
    public function getIssueContactsByIcon(): array {
        return $this->issueContactsByIcon;
    }

    /**
     * @param array $issueContactsByIcon
     */
    public function setIssueContactsByIcon(array $issueContactsByIcon): void {
        $this->issueContactsByIcon = $issueContactsByIcon;
    }

    /**
     * @return int
     */
    public function getIssueContactsCount(): int {
        return $this->issueContactsCount;
    }

    /**
     * @param int $issueContactsCount
     */
    public function setIssueContactsCount(int $issueContactsCount): void {
        $this->issueContactsCount = $issueContactsCount;
    }

    /**
     * @return int
     */
    public function getIssueProgressCount(): int {
        return $this->issueProgressCount;
    }

    /**
     * @param int $issueProgressCount
     */
    public function setIssueProgressCount(int $issueProgressCount): void {
        $this->issueProgressCount = $issueProgressCount;
    }

    /**
     * @return null|DateTime
     */
    public function getIssueLastContact(): ?DateTime {
        return $this->issueLastContact;
    }

    /**
     * @param null|DateTime $issueLastContact
     */
    public function setIssueLastContact(?DateTime $issueLastContact): void {
        $this->issueLastContact = $issueLastContact;
    }

    /**
     * @return DateTime
     */
    public function getIssueLastProgress(): ?DateTime {
        return $this->issueLastProgress;
    }

    /**
     * @param DateTime $issueLastProgress
     */
    public function setIssueLastProgress(?DateTime $issueLastProgress): void {
        $this->issueLastProgress = $issueLastProgress;
    }

    /**
     * @return string[]
     */
    public function getWaitingTodo(): array {
        return $this->waitingTodo;
    }

    /**
     * @param string[] $waitingTodo
     */
    public function setWaitingTodo(array $waitingTodo): void {
        $this->waitingTodo = $waitingTodo;
    }

}