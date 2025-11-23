<?php

namespace App\Action\Modules\Contacts;

use App\Attribute\ModuleAttribute;
use App\DTO\Modules\Contacts\ContactTypeDto;
use App\Entity\Modules\Contacts\MyContact;
use App\Entity\Modules\Contacts\MyContactType;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This route exists explicitly because the contact types logic is a mess, so types for
 * contact are getting handled separately from the contact itself.
 *
 * This is not handling the types itself but the types on contacts.
 */
#[Route("/module/contact/types", name: "module.contact.types")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_CONTACTS])]
class ContactTypesAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
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
        $dataArray = RequestService::tryFromJsonBody($request);
        $value     = ArrayHandler::get($dataArray, 'value', allowEmpty: false);
        $typeId    = ArrayHandler::get($dataArray, 'typeId', allowEmpty: false);
        $contactId = ArrayHandler::get($dataArray, 'contactId', allowEmpty: false);

        $contact    = $this->findContact($contactId);
        $typeEntity = $this->findType($typeId);

        $typesArr = json_decode($contact->getContacts(), true);
        $typesArr[] = [
            ContactTypeDto::KEY_NAME      => $typeEntity->getName(),
            ContactTypeDto::KEY_ICON_PATH => $typeEntity->getImagePath(),
            ContactTypeDto::KEY_DETAILS   => $value,
            ContactTypeDto::KEY_UUID      => Uuid::uuid4(),
        ];

        $contact->setContacts(json_encode($typesArr));
        $this->em->persist($contact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Request $request): JsonResponse
    {
        $dataArray = RequestService::tryFromJsonBody($request);
        $value     = ArrayHandler::get($dataArray, 'value');
        $uuid      = ArrayHandler::get($dataArray, 'uuid');
        $contactId = ArrayHandler::get($dataArray, 'contactId');
        $typeId    = ArrayHandler::get($dataArray, 'typeId');

        $contact    = $this->findContact($contactId);
        $typeEntity = $this->findType($typeId);

        $typesArr = json_decode($contact->getContacts(), true);
        foreach ($typesArr as &$type) {
            if ($type['uuid'] === $uuid) {
                $type[ContactTypeDto::KEY_DETAILS]   = $value;
                $type[ContactTypeDto::KEY_NAME]      = $typeEntity->getName();
                $type[ContactTypeDto::KEY_ICON_PATH] = $typeEntity->getImagePath();
                break;
            }
        }

        $contact->setContacts(json_encode($typesArr));
        $this->em->persist($contact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * This is a garbage solution, because the contact types logic is garbage. This might turn out to be slow
     * for LARGE amount of contacts/types, but other than that should be safe to got this way.
     *
     * @param string $uuid
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route("/{uuid}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(string $uuid): JsonResponse
    {
        $contact = $this->findContactForUuid($uuid);

        $typesArr = json_decode($contact->getContacts(), true);
        foreach ($typesArr as $idx => $type) {
            if ($type['uuid'] === $uuid) {
                unset($typesArr[$idx]);
                break;
            }
        }

        $typesArr = array_values($typesArr);
        $contact->setContacts(json_encode($typesArr));

        $this->em->persist($contact);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param int $id
     *
     * @return MyContact
     * @throws Exception
     */
    private function findContact(int $id): MyContact
    {
        $contact = $this->em->getRepository(MyContact::class)->find($id);
        if (is_null($contact)) {
            throw new Exception("No contact found for id: {$id}");
        }

        return $contact;
    }

    /**
     * @param string $uuid
     *
     * @return MyContact
     * @throws Exception
     */
    private function findContactForUuid(string $uuid): MyContact
    {
        $contact = $this->em->getRepository(MyContact::class)->findByUuid($uuid);
        if (is_null($contact)) {
            throw new Exception("No contact found for contact type uuid: {$uuid}");
        }

        return $contact;
    }

    /**
     * @param mixed $typeId
     *
     * @return MyContactType
     * @throws Exception
     */
    private function findType(int $typeId): MyContactType
    {
        $typeEntity = $this->em->getRepository(MyContactType::class)->find($typeId);
        if (is_null($typeEntity)) {
            throw new Exception("No type found for id: {$typeId}");
        }

        return $typeEntity;
    }

}