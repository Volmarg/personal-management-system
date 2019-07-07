<?php

namespace App\Controller\Modules\Car;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Form\Modules\Car\MyCarScheduleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyCarSettingsController extends AbstractController
{

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-car-settings", name="my_car_settings")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function display(Request $request) {
        $response = $this->submitForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }
        return $this->renderTemplate(true);
    }

    /**
     * @Route("/my-car-settings/schedule-type/remove", name="my_car_settings_schedule_car_remove")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CAR_SCHEDULES_TYPES_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-car-settings/schedule-type/update",name="my_car_settings_schedule_car_update")
     * @param Request $request
     * @return Response
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myCarSchedulesTypesRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    private function renderTemplate($ajax_render = false) {

        $form   = $this->getForm();
        $types  = $this->app->repositories->myCarSchedulesTypesRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-car/settings.html.twig',
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
    private function submitForm(Request $request) {
        /**
         * @var FormInterface $form
         */
        $form=$this->getForm();
        $form->handleRequest($request);
        /**
         * @var MyCarScheduleType $form_data
         */
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myCarSchedulesTypesRepository->findBy(['name' => $form_data->getName()])) {
            return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED, 200);
    }

    private function getForm() {
        return $this->createForm(MyCarScheduleType::class);
    }

}
