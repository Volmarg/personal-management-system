<?php

namespace App\Action\Modules\Contacts\Settings;

use App\Annotation\System\ModuleAnnotation;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/contacts/group", name: "module.contacts.group.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_CONTACTS])]
class GroupAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
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
        $groups = $this->em->getRepository(MyContactGroup::class)->getAllNotDeleted();

        $entriesData = [];
        foreach ($groups as $group) {
            $entriesData[] = [
                'id'    => $group->getId(),
                'name'  => $group->getName(),
                'color' => $group->getColor(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyContactGroup $group
     * @param Request        $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyContactGroup $group, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $group)->toJsonResponse();
    }

    /**
     * @param MyContactGroup $group
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyContactGroup $group): JsonResponse
    {
        $group->setDeleted(true);
        $this->em->persist($group);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request             $request
     * @param MyContactGroup|null $group
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyContactGroup $group = null): BaseResponse
    {
        $isNew = is_null($group);
        if ($isNew) {
            $group = new MyContactGroup();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $name      = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $color     = ArrayHandler::get($dataArray, 'color', true, 'BFDBFE');

        $entity = $this->em->getRepository(MyContactGroup::class)->getOneByName($name);

        // only allow saving already existing entity with unchanged name
        if ((!is_null($entity) && $isNew) || (!$isNew && $group->getName() !== $name && !is_null($entity)) ) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.contacts.settings.group.save.nameExist'));
        }

        $group->setName($name);
        $group->setColor(str_replace("#", "" , $color));
        $group->setIcon('');

        $this->em->persist($group);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}