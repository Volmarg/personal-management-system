<?php

namespace App\Action\Modules\Issues;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Issues\MyIssue;
use App\Repository\Modules\Issues\MyIssueRepository;
use App\Response\Base\BaseResponse;
use App\Services\Module\Issues\MyIssuesService;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-issues", name: "module.my_issues.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_ISSUES])]
class MyIssuesAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MyIssuesService        $issuesService,
        private readonly MyIssueRepository      $myIssueRepository,
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "new", methods: [Request::METHOD_POST])]
    public function new(Request $request): JsonResponse
    {
        $this->createOrUpdate($request);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @return JsonResponse
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allOngoingIssues = $this->myIssueRepository->findAllNotDeletedAndNotResolved();
        $issuesData       = $this->issuesService->getIssuesData($allOngoingIssues);

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($issuesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyIssue $issue
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyIssue $issue, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $issue);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyIssue $issue
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyIssue $issue): JsonResponse
    {
        $issue->setDeleted(true);
        $this->em->persist($issue);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * Create new entry or update existing
     *
     * @param Request      $request
     * @param MyIssue|null $issue
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyIssue $issue = null): void
    {
        if (!$issue) {
            $issue = new MyIssue();
        }

        $dataArray      = RequestService::tryFromJsonBody($request);
        $name           = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $information    = ArrayHandler::get($dataArray, 'information', allowEmpty: false);
        $isForDashboard = ArrayHandler::get($dataArray, 'isForDashboard');

        $issue->setName($name);
        $issue->setInformation($information);
        $issue->setShowOnDashboard($isForDashboard);

        $this->em->persist($issue);
        $this->em->flush();
    }

}