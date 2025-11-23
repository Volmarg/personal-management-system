<?php

namespace App\Action\Modules\Passwords;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
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

#[Route("/module/passwords", name: "module.passwords.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_PASSWORDS])]
class MyPasswordsAction extends AbstractController {

    public function __construct(
        private readonly MyPasswordsRepository  $passwordsRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $passwords = $this->passwordsRepository->findAllNotDeleted();

        $entriesData = [];
        foreach ($passwords as $password) {
            $entriesData[] = [
                'id'          => $password->getId(),
                'login'       => $password->getLogin(),
                'url'         => $password->getUrl(),
                'description' => $password->getDescription(),
                'groupId'     => $password->getGroup()->getId(),
                'groupName'   => $password->getGroup()->getName(),
                'password'    => $password->getPassword(),
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
     * @param MyPasswords $password
     * @param Request     $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyPasswords $password, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $password)->toJsonResponse();
    }

    /**
     * @param MyPasswords $password
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyPasswords $password): JsonResponse
    {
        $password->setDeleted(true);
        $this->em->persist($password);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request          $request
     * @param MyPasswords|null $password
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyPasswords $password = null): BaseResponse
    {
        $isNew = is_null($password);
        if ($isNew) {
            $password = new MyPasswords();
        }

        $dataArray      = RequestService::tryFromJsonBody($request);
        $login          = ArrayHandler::get($dataArray, 'login', allowEmpty: false);
        $url            = ArrayHandler::get($dataArray, 'url');
        $description    = ArrayHandler::get($dataArray, 'description', allowEmpty: false);
        $groupId        = ArrayHandler::get($dataArray, 'groupId', allowEmpty: false);
        $passwordString = ArrayHandler::get($dataArray, 'password', allowEmpty: false);

        $groupEntity = $this->em->find(MyPasswordsGroups::class, $groupId);
        if (is_null($groupEntity)) {
            throw new Exception("No password group exists for this id");
        }

        $password->setLogin($login);
        $password->setUrl($url);
        $password->setDescription($description);
        $password->setGroup($groupEntity);
        $password->setPassword($passwordString);

        $this->em->persist($password);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}