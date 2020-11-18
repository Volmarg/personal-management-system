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
     * todo: rename to create or update + change path/url in js entity + recompile
     *
     * @Route("/module-data-update", name="module-data-update")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createOrUpdate(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entity_id  = trim($parameters['id']);

        if( !empty($entity_id) ){
            $entity     = $this->controllers->getModuleDataController()->findOneById($entity_id);
            $response   = $this->app->repositories->update($parameters, $entity);
        }else{
            $response = $this->app->repositories->createAndSaveEntityForEntityClassAndArrayOfParameters(ModuleData::class, $parameters);
        }

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }


}