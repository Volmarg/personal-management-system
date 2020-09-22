<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use App\DTO\Modules\Issues\IssueCardDTO;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use App\Entity\Modules\Todo\MyTodo;
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
     * @param bool $include_deleted
     * @return IssueCardDTO[]
     * @throws Exception
     */
    public function buildIssuesCardsDtosFromIssues(array $issues, bool $include_deleted = false): array
    {
        $issues_cards_dtos    = [];
        $latest_contact_date  = null;
        $latest_progress_date = null;

        foreach( $issues as $issue ){

            /**
             * @var MyIssueContact[]  $issue_contacts
             * @var MyIssueProgress[] $issue_progresses
             */
            $issue_contacts   = $issue->getIssueContact()->getValues();
            $issue_progresses = $issue->getIssueProgress()->getValues();

            $issue_contacts_grouped_by_icon = [];
            $waiting_todo                   = [];

            if(
                    !empty($issue->getTodo())
                &&  ($issue->getTodo() instanceof MyTodo)
                &&  !empty($issue->getTodo()->getMyTodoElement())
            ){
                foreach($issue->getTodo()->getMyTodoElement() as $todo_element){
                    $waiting_todo[] = $todo_element->getName();
                }
            }

            $is_latest_contact_date_used = false;
            $latest_contact_date         = null;
            foreach($issue_contacts as $issue_contact){

                if(
                        !$include_deleted
                    &&  $issue_contact->isDeleted()
                )
                {
                    continue;
                }

                if( !$is_latest_contact_date_used ){
                    $latest_contact_date = $issue_contact->getDate();
                }
                $is_latest_contact_date_used = true;

                $icon = $issue_contact->getIcon();
                if( !array_key_exists($icon, $issue_contacts_grouped_by_icon) ){
                    $issue_contacts_grouped_by_icon[$icon] = [$issue_contact];
                }else{
                    $issue_contacts_grouped_by_icon[$icon][] = $issue_contact;
                }
            }

            if( !empty($issue_progresses) ){
                $latest_progress      = $issue_progresses[0];
                $latest_progress_date = $latest_progress->getDate();
            }

            $issue_contacts_count   = count($issue_contacts);
            $issue_progresses_count = count($issue_progresses);

            $issue_card_dto = new IssueCardDTO();
            $issue_card_dto->setIssue($issue);
            $issue_card_dto->setIssueContactsCount($issue_contacts_count);
            $issue_card_dto->setIssueProgressCount($issue_progresses_count);
            $issue_card_dto->setIssueContactsByIcon($issue_contacts_grouped_by_icon);
            $issue_card_dto->setIssueLastContact($latest_contact_date);
            $issue_card_dto->setIssueLastProgress($latest_progress_date);
            $issue_card_dto->setWaitingTodo($waiting_todo);

            $issues_cards_dtos[] = $issue_card_dto;
        }

        return $issues_cards_dtos;
    }

}