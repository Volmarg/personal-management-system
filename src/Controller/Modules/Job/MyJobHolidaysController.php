<?php

namespace App\Controller\Modules\Job;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class MyJobHolidaysController extends AbstractController
{

    const KEY_CHOICES = 'choices';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-job/holidays", name="my-job-holidays")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        $all_pools_years = $this->app->repositories->myJobHolidaysPoolRepository->getAllPoolsYears();

        $this->add($request, $all_pools_years);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false, $all_pools_years);
        }

        return $this->renderTemplate(true, $all_pools_years);
    }

    /**
     * @param bool $ajax_render
     * @param array $all_pools_years
     * @return Response
     */
    private function renderTemplate($ajax_render, array $all_pools_years) {

        $all_holidays_spent                 = $this->app->repositories->myJobHolidaysRepository->findBy(['deleted' => 0]);
        $job_holidays_summary               = $this->app->repositories->myJobHolidaysPoolRepository->getHolidaysSummaryGroupedByYears();
        $job_holidays_available_totally     = $this->app->repositories->myJobHolidaysPoolRepository->getAvailableDaysTotally();

        $job_holidays_form  = $this->app->forms->jobHolidays([
            static::KEY_CHOICES => $all_pools_years
        ]);

        $twig_data = [
            'ajax_render'                       => $ajax_render,
            'all_holidays_spent'                => $all_holidays_spent,
            'job_holidays_form'                 => $job_holidays_form->createView(),
            'job_holidays_summary'              => $job_holidays_summary,
            'job_holidays_available_totally'    => $job_holidays_available_totally
        ];

        return $this->render('modules/my-job/holidays.html.twig', $twig_data);
    }

    /**
     * @param Request $request
     * @param array $all_pools_years
     * @return void
     */
    public function add(Request $request, array $all_pools_years): void {

        $form = $this->app->forms->jobHolidays([
            static::KEY_CHOICES => $all_pools_years
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-job/holidays/update/",name="my-job-holidays-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myJobHolidaysRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;

    }

    /**
     * @Route("/my-job/holidays/remove/",name="my-job-holidays-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $all_pools_years = $this->app->repositories->myJobHolidaysPoolRepository->getAllPoolsYears();

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_HOLIDAYS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true, $all_pools_years);
        }
        return $response;
    }


}
