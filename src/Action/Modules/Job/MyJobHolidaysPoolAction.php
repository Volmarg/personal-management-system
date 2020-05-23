<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
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
    private $app;

    /**
     * @var MyJobSettingsAction $my_job_settings_action
     */
    private $my_job_settings_action;

    /**
     * @var Controllers $controllerss
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers, MyJobSettingsAction $my_job_settings_action) {
        $this->app                    = $app;
        $this->controllers            = $controllers;
        $this->my_job_settings_action = $my_job_settings_action;
    }

    /**
     * @Route("/my-job/holidays-pool/update/",name="my-job-holidays-pool-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myJobHolidaysPoolRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;

    }

    /**
     * @Route("/my-job/holidays-pool/remove/",name="my-job-holidays-pool-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {

            $rendered_template = $this->my_job_settings_action->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

}