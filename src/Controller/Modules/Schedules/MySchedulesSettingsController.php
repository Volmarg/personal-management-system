<?php

namespace App\Controller\Modules\Schedules;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MySchedulesSettingsController extends AbstractController
{

    const TWIG_TEMPLATE = 'modules/my-schedules/settings.html.twig';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-schedules-settings", name="my_schedules_settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $response = $this->addRecord($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }
        return $this->renderTemplate(true);
    }

    /**
     * @Route("/my-schedule-settings/schedule-type/remove", name="my_schedule_settings_schedule_remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_SCHEDULE_TYPE_REPOSITORY,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-schedule-settings/schedule-type/update",name="my_schedule_settings_schedule_update")
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myScheduleTypeRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderTemplate($ajax_render = false) {

        $form   = $this->app->forms->scheduleTypeForm();
        $types  = $this->app->repositories->myScheduleTypeRepository->findBy(['deleted' => 0]);

        return $this->render(self::TWIG_TEMPLATE,
            [
                'ajax_render'   => $ajax_render,
                'types'         => $types,
                'form'          => $form->createView()
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    private function addRecord(Request $request) {
        $form=$this->app->forms->scheduleTypeForm();
        $form->handleRequest($request);

        $form_data = $form->getData();

        // info: i can make general util with this
        if (!is_null($form_data) && $this->app->repositories->myScheduleTypeRepository->findBy(['name' => $form_data->getName()])) {
            return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED, 200);
    }

}
