<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use App\DTO\Modules\Issues\IssueCardDTO;
use App\Entity\Modules\Issues\MyIssue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssuesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @param MyIssue[] $issues
     * @return IssueCardDTO[]
     */
    public function buildIssuesCardsDtosFromIssues(array $issues): array
    {
        $issues_cards_dtos = [];

        foreach( $issues as $issue ){

            $issue_contacts   = $issue->getIssueContact()->getValues();
            $issue_progresses = $issue->getIssueProgress()->getValues();

            $issue_contacts_count   = count($issue_contacts);
            $issue_progresses_count = count($issue_progresses);

            $issue_card_dto = new IssueCardDTO();
            $issue_card_dto->setIssue($issue);
            $issue_card_dto->setIssueContactsCount($issue_contacts_count);
            $issue_card_dto->setIssueProgressCount($issue_progresses_count);

            $issues_cards_dtos[] = $issue_card_dto;
        }

        return $issues_cards_dtos;
    }

}