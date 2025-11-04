<?php

namespace App\Action\Modules\Contacts;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactGroup;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/contacts", name: "module.contacts.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_CONTACTS])]
class ContactsAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em
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
        return $this->createOrUpdate($request)->toJsonResponse();
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $contacts = $this->em->getRepository(MyContact::class)->findAllNotDeleted();

        $entriesData = [];
        foreach ($contacts as $contact) {
            $entriesData[] = [
                'id'          => $contact->getId(),
                'name'        => $contact->getName(),
                'groupId'     => $contact->getGroup()?->getId(),
                'groupName'   => $contact->getGroup()?->getName(),
                'groupColor'  => $contact->getGroup()?->getColor(),
                'imagePath'   => $contact->getImagePath(),
                'description' => $contact->getDescription(),
                'types'       => $contact->getContactTypesDto()->toArray(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyContact $contact
     * @param Request   $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyContact $contact, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $contact)->toJsonResponse();
    }

    /**
     * @param MyContact $contact
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyContact $contact): JsonResponse
    {
        $contact->setDeleted(true);
        $this->em->persist($contact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request        $request
     * @param MyContact|null $contact
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyContact $contact = null): BaseResponse
    {
        $isNew = is_null($contact);
        if ($isNew) {
            $contact = new MyContact();
            $contact->setContacts('');
            $contact->setDescriptionBackgroundColor('');
            $contact->setNameBackgroundColor('');
        }


        $dataArray   = RequestService::tryFromJsonBody($request);
        $name        = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $description = ArrayHandler::get($dataArray, 'description');
        $groupId     = ArrayHandler::get($dataArray, 'groupId', allowEmpty: false);
        $imagePath   = ArrayHandler::get($dataArray, 'imagePath');

        $group = $this->em->find(MyContactGroup::class, $groupId);
        if (is_null($group)) {
            throw new Exception("No group was found for id: {$groupId}");
        }

        $contact->setName($name);
        $contact->setDescription($description);
        $contact->setGroup($group);
        $contact->setImagePath($imagePath);

        $this->em->persist($contact);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}