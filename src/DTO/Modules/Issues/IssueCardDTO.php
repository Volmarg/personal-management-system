<?php

namespace App\DTO\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;

class IssueCardDTO {

    /**
     * @var MyIssue $issue
     */
    private $issue;

    /**
     * @var array $issue_contacts_count_by_icon
     */
    private $issue_contacts_count_by_icon = [];

    /**
     * @var int $issue_contacts_count
     */
    private $issue_contacts_count = 0;

    /**
     * @var int $issue_progress_count
     */
    private $issue_progress_count = 0;

    /**
     * @var array $issue_last_contact
     */
    private $issue_last_contact = [];

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
    public function getIssueContactsCountByIcon(): array {
        return $this->issue_contacts_count_by_icon;
    }

    /**
     * @param array $issue_contacts_count_by_icon
     */
    public function setIssueContactsCountByIcon(array $issue_contacts_count_by_icon): void {
        $this->issue_contacts_count_by_icon = $issue_contacts_count_by_icon;
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
     * @return array
     */
    public function getIssueLastContact(): array {
        return $this->issue_last_contact;
    }

    /**
     * @param array $issue_last_contact
     */
    public function setIssueLastContact(array $issue_last_contact): void {
        $this->issue_last_contact = $issue_last_contact;
    }

}