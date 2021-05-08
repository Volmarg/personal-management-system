<?php

namespace App\Controller\Modules\Issues;

use App\Controller\Core\Application;
use App\Controller\Modules\ModulesController;
use App\Controller\Page\SettingsLockModuleController;
use App\Controller\System\LockedResourceController;
use App\DTO\Modules\Issues\IssueCardDTO;
use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
use App\Entity\Modules\Issues\MyIssueProgress;
use App\Entity\Modules\Todo\MyTodo;
use App\Entity\System\LockedResource;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyIssuesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(Application $app, LockedResourceController $lockedResourceController) {
        $this->lockedResourceController = $lockedResourceController;
        $this->app                      = $app;
    }

    /**
     * @param MyIssue[] $issues
     * @param bool $includeDeleted
     * @return IssueCardDTO[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildIssuesCardsDtosFromIssues(array $issues, bool $includeDeleted = false): array
    {
        $issuesCardsDtos    = [];
        $latestProgressDate = null;

        foreach( $issues as $issue ){

            /**
             * @var MyIssueContact[]  $issueContacts
             * @var MyIssueProgress[] $issueProgresses
             */
            $issueContacts   = $issue->getIssueContact()->getValues();
            $issueProgresses = $issue->getIssueProgress()->getValues();

            $issueContactsGroupedByIcon = [];
            $waitingTodo                = [];

            if(
                    !empty($issue->getTodo())
                &&  ($issue->getTodo() instanceof MyTodo)
                &&  !empty($issue->getTodo()->getMyTodoElement())
                &&  $this->lockedResourceController->isAllowedToSeeResource($issue->getTodo()->getId(), LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_TODO, false)
            ){
                foreach($issue->getTodo()->getMyTodoElement() as $todoElement){
                    if( !$todoElement->getCompleted() ){
                        $waitingTodo[] = $todoElement->getName();
                    }
                }
            }

            $isLatestContactDateUsed = false;
            $latestContactDate       = null;
            foreach($issueContacts as $issueContact){

                if(
                        !$includeDeleted
                    &&  $issueContact->isDeleted()
                )
                {
                    continue;
                }

                if( !$isLatestContactDateUsed ){
                    $latestContactDate = $issueContact->getDate();
                }
                $isLatestContactDateUsed = true;

                $icon = $issueContact->getIcon();
                if( !array_key_exists($icon, $issueContactsGroupedByIcon) ){
                    $issueContactsGroupedByIcon[$icon] = [$issueContact];
                }else{
                    $issueContactsGroupedByIcon[$icon][] = $issueContact;
                }
            }

            if( !empty($issueProgresses) ){
                $latestProgress     = $issueProgresses[0];
                $latestProgressDate = $latestProgress->getDate();
            }

            $issueContactsCount   = count($issueContacts);
            $issueProgressesCount = count($issueProgresses);

            $issueCardDto = new IssueCardDTO();
            $issueCardDto->setIssue($issue);
            $issueCardDto->setIssueContactsCount($issueContactsCount);
            $issueCardDto->setIssueProgressCount($issueProgressesCount);
            $issueCardDto->setIssueContactsByIcon($issueContactsGroupedByIcon);
            $issueCardDto->setIssueLastContact($latestContactDate);
            $issueCardDto->setIssueLastProgress($latestProgressDate);
            $issueCardDto->setWaitingTodo($waitingTodo);

            $issuesCardsDtos[] = $issueCardDto;
        }

        return $issuesCardsDtos;
    }

    /**
     * Returns one Entity or null for given id
     * @param int $entityId
     * @return MyIssue|null
     */
    public function findIssueById(int $entityId): ?MyIssue
    {
        return $this->app->repositories->myIssueRepository->findIssueById($entityId);
    }

    /**
     * @param int|null $orderByFieldEntityId
     * @return MyIssue[]
     */
    public function findAllNotDeletedAndNotResolved(int $orderByFieldEntityId = null): array
    {
        return $this->app->repositories->myIssueRepository->findAllNotDeletedAndNotResolved($orderByFieldEntityId);
    }

    /**
     * @param MyIssue $issue
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssue(MyIssue $issue)
    {
        $this->app->repositories->myIssueRepository->saveIssue($issue);
    }

    /**
     * @param MyIssueProgress $myIssueProgress
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssueProgress(MyIssueProgress $myIssueProgress): void
    {
        $this->app->repositories->myIssueRepository->saveIssueProgress($myIssueProgress);
    }

    /**
     * @param MyIssueContact $myIssueContact
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveIssueContact(MyIssueContact $myIssueContact): void
    {
        $this->app->repositories->myIssueRepository->saveIssueContact($myIssueContact);
    }

}