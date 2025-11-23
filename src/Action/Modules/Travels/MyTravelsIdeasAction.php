<?php

namespace App\Action\Modules\Travels;

use App\Attribute\ModuleAttribute;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
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

#[Route("/module/travels/ideas", name: "module.travels.ideas.")]
#[ModuleAttribute(values: ["name" => ModulesService::MODULE_NAME_TRAVELS])]
class MyTravelsIdeasAction extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly MyTravelsIdeasRepository $travelsIdeasRepository,
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
        $allIdeas = $this->travelsIdeasRepository->getAllNotDeleted();

        $entriesData = [];
        foreach ($allIdeas as $idea) {
            $entriesData[] = [
                'id'       => $idea->getId(),
                'location' => $idea->getLocation(),
                'country'  => $idea->getCountry(),
                'imageUrl' => $idea->getImage(),
                'mapUrl'   => $idea->getMap(),
                'category' => $idea->getCategory(),
            ];
        }

        $response = BaseResponse::buildOkResponse();
        $response->setAllRecordsData($entriesData);

        return $response->toJsonResponse();
    }

    /**
     * @param MyTravelsIdeas $idea
     * @param Request        $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route("/{id}", name: "update", methods: [Request::METHOD_PATCH])]
    public function update(MyTravelsIdeas $idea, Request $request): JsonResponse
    {
        return $this->createOrUpdate($request, $idea)->toJsonResponse();
    }

    /**
     * @param MyTravelsIdeas $idea
     *
     * @return JsonResponse
     */
    #[Route("/{id}", name: "remove", methods: [Request::METHOD_DELETE])]
    public function remove(MyTravelsIdeas $idea): JsonResponse
    {
        $idea->setDeleted(true);
        $this->em->persist($idea);
        $this->em->flush();

        return BaseResponse::buildOkResponse()->toJsonResponse();
    }

    /**
     * @param Request             $request
     * @param MyTravelsIdeas|null $idea
     *
     * @return BaseResponse
     * @throws Exception
     */
    private function createOrUpdate(Request $request, ?MyTravelsIdeas $idea = null): BaseResponse
    {
        $isNew = is_null($idea);
        if ($isNew) {
            $idea = new MyTravelsIdeas();
        }

        $dataArray = RequestService::tryFromJsonBody($request);
        $location  = ArrayHandler::get($dataArray, 'location', allowEmpty: false);
        $country   = ArrayHandler::get($dataArray, 'country', allowEmpty: false);
        $imageUrl  = ArrayHandler::get($dataArray, 'imageUrl', allowEmpty: false);
        $mapUrl    = ArrayHandler::get($dataArray, 'mapUrl', true, '');
        $category  = ArrayHandler::get($dataArray, 'category', allowEmpty: false);

        $idea->setLocation($location);
        $idea->setCountry($country);
        $idea->setImage($imageUrl);
        $idea->setMap($mapUrl);
        $idea->setCategory($category);

        $this->em->persist($idea);
        $this->em->flush();

        return BaseResponse::buildOkResponse();
    }

}