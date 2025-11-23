<?php

namespace App\Action\Modules\Achievements;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Achievements\Achievement;
use App\Repository\Modules\Achievements\AchievementRepository;
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

#[Route("/module/achievements", name: "module.achievements.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_ACHIEVEMENTS])]
class AchievementAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AchievementRepository $achievementRepository
    )
    {
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
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $allAchievements = $this->achievementRepository->getAllNotDeleted();
        $entriesData     = [
            Achievement::ENUM_HARDCORE => [],
            Achievement::ENUM_HARD     => [],
            Achievement::ENUM_MEDIUM   => [],
            Achievement::ENUM_SIMPLE   => [],
        ];

        foreach ($allAchievements as $achievement) {
            $entriesData[$achievement->getType()][] = [
                'id'          => $achievement->getId(),
                'name'        => $achievement->getName(),
                'description' => $achievement->getDescription(),
                'type'        => $achievement->getType(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param Achievement $achievement
     * @param Request     $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(Achievement $achievement, Request $request): JsonResponse
    {
        $this->createOrUpdate($request, $achievement);
        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Achievement $achievement
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(Achievement $achievement): JsonResponse
    {
        $achievement->setDeleted(true);
        $this->em->persist($achievement);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request          $request
     * @param Achievement|null $achievement
     *
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?Achievement $achievement = null): void
    {
        if (!$achievement) {
            $achievement = new Achievement();
        }

        $dataArray   = RequestService::tryFromJsonBody($request);
        $name        = ArrayHandler::get($dataArray, 'name', allowEmpty: false);
        $description = ArrayHandler::get($dataArray, 'description');
        $type        = ArrayHandler::get($dataArray, 'type', allowEmpty: false);

        $achievement->setName($name);
        $achievement->setDescription($description);
        $achievement->setType($type);

        $this->em->persist($achievement);
        $this->em->flush();
    }

}