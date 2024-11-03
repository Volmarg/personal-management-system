<?php

namespace App\Action\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueProgress;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/my-issues/progress", name: "module.my_issues.progress.")]
class MyIssuesProgress extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {

    }

    /**
     * @param MyIssue $issue
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "new", methods: [Request::METHOD_POST])]
    public function new(MyIssue $issue, Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date');
        $information = ArrayHandler::get($dataArray, 'information');

        $date = new DateTime($dateString);

        $progress = new MyIssueProgress();
        $progress->setInformation($information);
        $progress->setDate($date);

        $issue->addIssueProgress($progress);
        $progress->setIssue($issue);

        $this->em->persist($issue);
        $this->em->persist($progress);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyIssueProgress $issueProgress
     * @param Request         $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function update(MyIssueProgress $issueProgress, Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date');
        $information = ArrayHandler::get($dataArray, 'information');

        $date = new DateTime($dateString);

        $issueProgress->setInformation($information);
        $issueProgress->setDate($date);

        $this->em->persist($issueProgress);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyIssueProgress $issueProgress
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function remove(MyIssueProgress $issueProgress): JsonResponse
    {
        $issueProgress->setDeleted(true);
        $this->em->persist($issueProgress);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

}