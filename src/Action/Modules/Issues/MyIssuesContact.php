<?php

namespace App\Action\Modules\Issues;

use App\Entity\Modules\Issues\MyIssue;
use App\Entity\Modules\Issues\MyIssueContact;
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

#[Route("/module/my-issues/contact", name: "module.my_issues.contact.")]
class MyIssuesContact extends AbstractController
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
    #[Route("/{id}", name: "new", requirements: ['id' => '\d+'], methods: [Request::METHOD_POST])]
    public function new(MyIssue $issue, Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date');
        $information = ArrayHandler::get($dataArray, 'information');

        $date = new DateTime($dateString);

        $contact = new MyIssueContact();
        $contact->setInformation($information);
        $contact->setDate($date);
        $contact->setIssue($issue);

        $issue->addIssueContact($contact);

        $this->em->persist($contact);
        $this->em->persist($issue);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyIssueContact $issueContact
     * @param Request        $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function update(MyIssueContact $issueContact, Request $request): JsonResponse
    {
        $dataArray   = RequestService::tryFromJsonBody($request);
        $dateString  = ArrayHandler::get($dataArray, 'date');
        $information = ArrayHandler::get($dataArray, 'information');

        $date = new DateTime($dateString);

        $issueContact->setInformation($information);
        $issueContact->setDate($date);

        $this->em->persist($issueContact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param MyIssueContact $issueContact
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function remove(MyIssueContact $issueContact): JsonResponse
    {
        $issueContact->setDeleted(true);
        $this->em->persist($issueContact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

}