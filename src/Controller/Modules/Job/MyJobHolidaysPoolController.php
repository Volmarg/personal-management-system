<?php

namespace App\Controller\Modules\Job;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class MyJobHolidaysPoolController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var MyJobSettingsController  $my_jobs_settings_controller
     */
    private $my_jobs_settings_controller;

    public function __construct(Application $app, MyJobSettingsController $my_jobs_settings_controller) {
        $this->app                          = $app;
        $this->my_jobs_settings_controller  = $my_jobs_settings_controller;
    }

    /**
     * @Route("/my-job/holidays-pool/update/",name="my-job-holidays-pool-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {

            $rendered_template = $this->my_jobs_settings_controller->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }


}
