<?php

namespace App\Controller\Modules\Car;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Car\MyCar;
use App\Form\Modules\Car\MyCarSchedule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MyCarController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-car", name="my-car")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($this->getForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($this->getForm(), false);
        }

        return $this->renderTemplate($this->getForm(), true);
    }

    /**
     * @param $form
     * @param bool $ajax_render
     * @return Response
     */
    protected function renderTemplate($form, $ajax_render = false) {
        $car_schedule_form_view = $form->createView();

        $column_names = $this->getDoctrine()->getManager()->getClassMetadata(MyCar::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $car_all_data            = $this->app->repositories->myCarRepository->findBy(['deleted' => 0]);
        $car_all_schedules_types = $this->app->repositories->myCarSchedulesTypesRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-car/my-car.html.twig',
            compact(
                'column_names',
                'car_all_data',
                'car_schedule_form_view',
                'ajax_render',
                'car_all_schedules_types'
            )
        );
    }

    private function getForm() {
        return $this->createForm(MyCarSchedule::class);
    }

    /**
     * @param $car_schedule_form
     * @param Request $request
     */
    protected function addFormDataToDB($car_schedule_form, Request $request): void {
        $car_schedule_form->handleRequest($request);

        if ($car_schedule_form->isSubmitted() && $car_schedule_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($car_schedule_form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-car/update/",name="my-car-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->getDoctrine()->getRepository(MyCar::class)->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-car/remove/",name="my-car-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeMyCarInDB(Request $request): Response {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CAR_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($this->getForm(), true);
        }
        return $response;
    }


}
