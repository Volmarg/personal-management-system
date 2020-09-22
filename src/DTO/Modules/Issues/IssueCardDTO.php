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
     * @var array $issue_contacts_by_icon
     */
    private $issue_contacts_by_icon = [];

    /**
     * @var int $issue_contacts_count
     */
    private $issue_contacts_count = 0;

    /**
     * @var int $issue_progress_count
     */
    private $issue_progress_count = 0;

    /**
     * @var DateTime $issue_last_contact
     */
    private $issue_last_contact = null;

    /**
     * @var DateTime $issue_last_progress
     */
    private $issue_last_progress = null;

    /**
     * @var string[] $waiting_todo
     */
    private $waiting_todo = [];

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
        return $this->issue_contacts_by_icon;
    }

    /**
     * @param array $issue_contacts_by_icon
     */
    public function setIssueContactsByIcon(array $issue_contacts_by_icon): void {
        $this->issue_contacts_by_icon = $issue_contacts_by_icon;
    }

    /**
     * @return int
     */
    public function getIssueContactsCount(): int {
        return $this->issue_contacts_count;
    }

    /**
     * @param int $issue_contacts_count
     */
    public function setIssueContactsCount(int $issue_contacts_count): void {
        $this->issue_contacts_count = $issue_contacts_count;
    }

    /**
     * @return int
     */
    public function getIssueProgressCount(): int {
        return $this->issue_progress_count;
    }

    /**
     * @param int $issue_progress_count
     */
    public function setIssueProgressCount(int $issue_progress_count): void {
        $this->issue_progress_count = $issue_progress_count;
    }

    /**
     * @return null|DateTime
     */
    public function getIssueLastContact(): ?DateTime {
        return $this->issue_last_contact;
    }

    /**
     * @param null|DateTime $issue_last_contact
     */
    public function setIssueLastContact(?DateTime $issue_last_contact): void {
        $this->issue_last_contact = $issue_last_contact;
    }

    /**
     * @return DateTime
     */
    public function getIssueLastProgress(): ?DateTime {
        return $this->issue_last_progress;
    }

    /**
     * @param DateTime $issue_last_progress
     */
    public function setIssueLastProgress(?DateTime $issue_last_progress): void {
        $this->issue_last_progress = $issue_last_progress;
    }

    /**
     * @return string[]
     */
    public function getWaitingTodo(): array {
        return $this->waiting_todo;
    }

    /**
     * @param string[] $waiting_todo
     */
    public function setWaitingTodo(array $waiting_todo): void {
        $this->waiting_todo = $waiting_todo;
    }

}