<?php

namespace App\Action\Modules\Passwords;

use App\Controller\Modules\ModulesController;
use App\Controller\Modules\Passwords\MyPasswordsGroupsController;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use App\Response\Base\BaseResponse;
use App\Services\RequestService;
use App\Services\TypeProcessor\ArrayHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/module/passwords/settings/group", name: "module.passwords.list.")]
#[ModuleAnnotation(values: ["name" => ModulesController::MODULE_NAME_PASSWORDS])]
class MyPasswordsGroupsAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly MyPasswordsGroupsController $passwordsGroupsController,
        private readonly TranslatorInterface         $translator
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $groups = $this->passwordsGroupsController->findAllNotDeleted();

        $entriesData = [];
        foreach ($groups as $group) {
            $entriesData[] = [
                'id'   => $group->getId(),
                'name' => $group->getName(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
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
     * @param MyPasswordsGroups $group
     * @param Request           $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPasswordsGroups $group, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $group)->toJsonResponse();
    }

    /**
     * @param MyPasswordsGroups $group
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPasswordsGroups $group): JsonResponse
    {
        $group->setDeleted(true);
        $this->em->persist($group);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request                $request
     * @param MyPasswordsGroups|null $group
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPasswordsGroups $group = null): BaseResponse
    {
        $isNew = is_null($group);
        if ($isNew) {
            $group = new MyPasswordsGroups();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $name      = ArrayHandler::get($dataArray, 'name');

        if (!is_null($this->passwordsGroupsController->findOneByName($name))) {
            return BaseResponse::buildBadRequestErrorResponse($this->translator->trans('module.passwords.groups.createdUpdate.nameExist'));
        }

        $group->setName($name);

        $this->em->persist($group);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}