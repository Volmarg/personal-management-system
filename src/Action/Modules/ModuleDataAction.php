<?php

namespace App\Action\Modules;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Entity\Modules\ModuleData;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ModuleDataAction extends AbstractController
{

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers)
    {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/module-data-create-or-update", name="module-data-create-or-update")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createOrUpdate(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        if( !empty($entityId) ){
            $entity     = $this->controllers->getModuleDataController()->findOneById($entityId);
            $response   = $this->app->repositories->update($parameters, $entity);
        }else{
            $response = $this->app->repositories->createAndSaveEntityForEntityClassAndArrayOfParameters(ModuleData::class, $parameters);
        }

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }


}