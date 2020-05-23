<?php

namespace App\Action\Modules\Schedules;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Schedules\MySchedule;
use App\Form\Modules\Schedules\MyScheduleType;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MySchedulesAction extends AbstractController {


    const TWIG_TEMPLATE = 'modules/my-schedules/my-schedules.twig';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-schedules/{schedules_type}", name="my_schedules")
     * @param Request $request
     * @param string $schedules_type
     * @return Response
     * @throws Exception
     */
    public function display(Request $request, string $schedules_type):Response {
        $this->controllers->getMySchedulesController()->validateSchedulesType($schedules_type);

        $this->addFormDataToDB($request, $schedules_type);

        if ( !$request->isXmlHttpRequest() ) {
            return $this->renderTemplate($schedules_type, false);
        }

        $template_content  = $this->renderTemplate($schedules_type, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param string $schedules_type
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    protected function renderTemplate(string $schedules_type, bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form = $form = $this->app->forms->scheduleForm([MyScheduleType::KEY_PARAM_SCHEDULES_TYPES => $schedules_type]);

        $schedules       = $this->app->repositories->myScheduleRepository->getSchedulesByScheduleTypeName($schedules_type);
        $schedules_types = $this->app->repositories->myScheduleTypeRepository->findBy(['deleted' => 0]);

        $data = [
            'form'                           => $form->createView(),
            'ajax_render'                    => $ajax_render,
            'schedules'                      => $schedules,
            'schedules_types'                => $schedules_types,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

    /**
     * @param Request $request
     * @param string $schedules_type
     */
    private function addFormDataToDB(Request $request, string $schedules_type): void {

        $form = $this->app->forms->scheduleForm([MyScheduleType::KEY_PARAM_SCHEDULES_TYPES => $schedules_type]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-schedule/update/",name="my-schedule-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->getDoctrine()->getRepository(MySchedule::class)->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-schedule/remove/",name="my-schedule-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeScheduleById(Request $request): Response {

        $id             = $request->request->get('id');
        $schedule       = $this->app->repositories->myScheduleRepository->find($id);
        $schedules_type = $schedule->getScheduleType()->getName();

        $response = $this->app->repositories->deleteById(Repositories::MY_SCHEDULE_REPOSITORY, $id);

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate($schedules_type, true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }


}