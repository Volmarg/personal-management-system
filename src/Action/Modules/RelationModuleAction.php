<?php

namespace App\Action\Modules;

use App\Annotation\System\ModuleAnnotation;
use App\Entity\System\Module;
use App\Response\Base\BaseResponse;
use App\Services\Module\ModulesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/module/relation", name: "module.relation.")]
#[ModuleAnnotation(values: ["name" => ModulesService::MODULE_NAME_ISSUES])]
class RelationModuleAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route("/all", name: "get_all", methods: [Request::METHOD_GET])]
    public function getAll(): JsonResponse
    {
        $entries = [];
        foreach ($this->em->getRepository(Module::class)->findAll() as $module) {
            $entries[] = [
                'id'   => $module->getId(),
                'name' => $module->getName(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entries);

        return $response->toJsonResponse();
    }

}