<?php

namespace App\Controller\Modules\Todo;

use App\Controller\Core\Application;
use App\Controller\Modules\Issues\MyIssuesController;
use App\Controller\System\LockedResourceController;
use App\Entity\Modules\Todo\MyTodo;
use App\Enum\RelatableModuleEnum;
use App\Repository\Modules\Issues\MyIssueRepository;
use App\Repository\System\ModuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyTodoController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var MyIssuesController $issuesController
     */
    private MyIssuesController $issuesController;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private LockedResourceController $lockedResourceController;

    public function __construct(
        Application $app,
        MyIssuesController $issuesController,
        LockedResourceController $lockedResourceController,
        private readonly ModuleRepository $moduleRepository,
        private readonly MyIssueRepository $issueRepository,
    )
    {
        $this->app                      = $app;
        $this->issuesController         = $issuesController;
        $this->lockedResourceController = $lockedResourceController;
    }

    /**
     * Returns array of modules with underlying entries that can be related to the todo module
     *
     * @return array|array[]
     */
    public function getPossibleRelationEntries(array $includedIds = []): array
    {
        $allEntries = [
            'modules' => [],
        ];

        // can relate to module only
        $goalModule = $this->moduleRepository->getOneByName(RelatableModuleEnum::MY_GOALS->value);
        if ($goalModule) {
            $moduleData = [
                'id'   => $goalModule->getId(),
                'name' => $goalModule->getName(),
            ];

            $allEntries['modules'][] = $moduleData;
        }

        // can relate to module and entries
        $issuesModule = $this->moduleRepository->getOneByName(RelatableModuleEnum::MY_ISSUES->value);
        if ($issuesModule) {
            $moduleData = [
                'id'   => $issuesModule->getId(),
                'name' => $issuesModule->getName(),
            ];

            $entries = [];
            $issues  = $this->issueRepository->findAllAssignable(false, $includedIds);
            foreach ($issues as $issue) {
                $entries[] = [
                    'id'   => $issue->getId(),
                    'name' => $issue->getName(),
                ];
            }

            $moduleData['entries']   = $entries;
            $allEntries['modules'][] = $moduleData;
        }

        return $allEntries;
    }

    /**
     * @param MyTodo[] $allTodo
     *
     * @return array
     */
    public function buildFrontDataArray(array $allTodo): array
    {
        $entriesData = [];
        foreach($allTodo as $todo){
            $elements = [];
            foreach ($todo->getMyTodoElement() as $element) {
                if ($element->isDeleted()) {
                    continue;
                }

                $elements[] = [
                    'id' => $element->getId(),
                    'name' => $element->getName() ?? '',
                    'isDone' => $element->getCompleted() ?? false,
                ];
            }

            $entriesData[] = [
                'id'              => $todo->getId(),
                'name'            => $todo->getName() ?? '',
                'description'     => $todo->getDescription() ?? '',
                'showOnDashboard' => $todo->getDisplayOnDashboard() ?? false,
                'elements'        => $elements,
                'module'          => [
                    'id'   => $todo->getModule()?->getId() ?? null,
                    'name' => $todo->getModule()?->getName() ?? null,
                    'entryId' => $todo->getRelatedEntityId()
                ],
            ];
        }

        return $entriesData;
    }

}