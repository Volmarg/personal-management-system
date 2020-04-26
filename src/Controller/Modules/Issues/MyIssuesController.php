<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use App\DTO\Modules\Issues\IssueCardDTO;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use DateTime;
use Exception;
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
     * @throws Exception
     */
    public function buildIssuesCardsDtosFromIssues(array $issues): array
    {
        $issues_cards_dtos = [];

        foreach( $issues as $issue ){

            /**
             * @var MyIssueContact[]  $issue_contacts
             * @var MyIssueProgress[] $issue_progresses
             */
            $issue_contacts   = $issue->getIssueContact()->getValues();
            $issue_progresses = $issue->getIssueProgress()->getValues();

            $issue_contacts_grouped_by_icon = [];

            foreach($issue_contacts as $issue_contact){

                $icon = $issue_contact->getIcon();
                if( !array_key_exists($icon, $issue_contacts_grouped_by_icon) ){
                    $issue_contacts_grouped_by_icon[$icon] = [$issue_contact];
                }else{
                    $issue_contacts_grouped_by_icon[$icon][] = $issue_contact;
                }
            }

            $issue_contacts_count   = count($issue_contacts);
            $issue_progresses_count = count($issue_progresses);

            $issue_card_dto = new IssueCardDTO();
            $issue_card_dto->setIssue($issue);
            $issue_card_dto->setIssueContactsCount($issue_contacts_count);
            $issue_card_dto->setIssueProgressCount($issue_progresses_count);
            $issue_card_dto->setIssueContactsByIcon($issue_contacts_grouped_by_icon);
            $issue_card_dto->setIssueLastContact(new DateTime());

            $issues_cards_dtos[] = $issue_card_dto;
        }

        return $issues_cards_dtos;
    }

}