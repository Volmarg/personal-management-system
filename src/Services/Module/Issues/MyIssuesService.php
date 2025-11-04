<?php

namespace App\Services\Module\Issues;

use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\LockedResource;
use App\Services\Module\ModulesService;
use App\Services\System\LockedResourceService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssuesService extends AbstractController {

    /**
     * @var LockedResourceService $lockedResourceService
     */
    private LockedResourceService $lockedResourceService;

    public function __construct(
        LockedResourceService $lockedResourceService,
    ) {
        $this->lockedResourceService = $lockedResourceService;
    }

    /**
     * @param MyIssue[] $issues
     *
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getIssuesData(array $issues): array
    {
        $allIssuesData = [];
        foreach ($issues as $issue) {
            $contacts = [];
            $progress = [];
            $todo     = [];

            /**
             * @var MyIssueContact[]  $issueContacts
             * @var MyIssueProgress[] $issueProgresses
             */
            $issueContacts   = $issue->getIssueContact()->getValues();
            $issueProgresses = $issue->getIssueProgress()->getValues();

            if (
                    ($issue->getTodo() instanceof MyTodo)
                &&  $this->lockedResourceService->isAllowedToSeeResource($issue->getTodo()->getId(), LockedResource::TYPE_ENTITY, ModulesService::MODULE_NAME_TODO, false)
            ){
                $todoElements = [];
                foreach ($issue->getTodo()->getMyTodoElement() as $todoElement) {
                    $todoElements[] = [
                        'id'     => $todoElement->getId(),
                        'name'   => $todoElement->getName(),
                        'isDone' => $todoElement->getCompleted(),
                    ];
                }

                $todo = [
                    'id'              => $issue->getTodo()->getId(),
                    'name'            => $issue->getTodo()->getName(),
                    'description'     => $issue->getTodo()->getDescription(),
                    'showOnDashboard' => $issue->getTodo()->getDisplayOnDashboard(),
                    'elements'        => $todoElements,
                ];
            }

            foreach ($issueContacts as $contact) {
                if ($contact->isDeleted()) {
                    continue;
                }

                $contacts[] = [
                    'id'          => $contact->getId(),
                    'description' => $contact->getInformation(),
                    'date'        => $contact->getDate()?->format('Y-m-d H:i:s'),
                ];
            }

            foreach ($issueProgresses as $oneProgress) {
                if ($oneProgress->isDeleted()) {
                    continue;
                }

                $progress[] = [
                    'id'          => $oneProgress->getId(),
                    'description' => $oneProgress->getInformation(),
                    'date'        => $oneProgress->getDate()?->format('Y-m-d H:i:s'),
                ];
            }

            $allIssuesData[] = [
                'id'             => $issue->getId(),
                'name'           => $issue->getName(),
                'description'    => $issue->getInformation(),
                'hasRelatedTodo' => !empty($todo),
                'isForDashboard' => $issue->isShowOnDashboard(),
                'todo'           => $todo,
                'contacts'       => $contacts,
                'progress'       => $progress,
            ];
        }

        return $allIssuesData;
    }

}