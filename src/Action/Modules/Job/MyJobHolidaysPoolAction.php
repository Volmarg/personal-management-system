<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyJobHolidaysPoolAction extends AbstractController {

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var MyJobSettingsAction $myJobSettingsAction
     */
    private MyJobSettingsAction $myJobSettingsAction;

    /**
     * @var Controllers $controllerss
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers, MyJobSettingsAction $myJobSettingsAction) {
        $this->app                 = $app;
        $this->controllers         = $controllers;
        $this->myJobSettingsAction = $myJobSettingsAction;
    }

    /**
     * @Route("/my-job/holidays-pool/update/",name="my-job-holidays-pool-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     * @throws NonUniqueResultException
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyJobHolidaysPoolController()->findOneEntityById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-job/holidays-pool/remove/",name="my-job-holidays-pool-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {

            $renderedTemplate = $this->myJobSettingsAction->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

}