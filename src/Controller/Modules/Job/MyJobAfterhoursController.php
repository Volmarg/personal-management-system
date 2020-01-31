<?php

namespace App\Controller\Modules\Job;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Job\MyJobAfterhours;
use App\Form\Modules\Job\MyJobAfterhoursType;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MyJobAfterhoursController extends AbstractController {

    const GENERAL_USAGE = 'general usage';

    /**
     * @var array $entity_enums
     */
    private $entity_enums = [];

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $entity_enums       = [MyJobAfterhours::TYPE_MADE, MyJobAfterhours::TYPE_SPENT];

        $this->entity_enums = array_combine(
            array_map('ucfirst', array_values($entity_enums)),
            $entity_enums
        );

        $this->app   = $app;
    }

    /**
     * @Route("/my-job/afterhours", name="my-job-afterhours")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    protected function renderTemplate($ajax_render = false) {

        $form                 = $this->getForm();
        $afterhours_form_view = $form->createView();

        $column_names       = $this->getDoctrine()->getManager()->getClassMetadata(MyJobAfterhours::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $afterhours_all     = $this->app->repositories->myJobAfterhoursRepository->findBy(['deleted' => 0]);
        $afterhours_spent   = $this->filterAfterhours($afterhours_all, MyJobAfterhours::TYPE_SPENT);
        $afterhours_made    = $this->filterAfterhours($afterhours_all, MyJobAfterhours::TYPE_MADE);

        $remaining_time_to_spend_per_goal = $this->getTimeToSpend();

        $twig_data = [
            'afterhours_form_view'              => $afterhours_form_view,
            'column_names'                      => $column_names,
            'afterhours_all'                    => $afterhours_all,
            'afterhours_spent'                  => $afterhours_spent,
            'afterhours_made'                   => $afterhours_made,
            'remaining_time_to_spend_per_goal'  => $remaining_time_to_spend_per_goal,
            'ajax_render'                       => $ajax_render,
        ];

        return $this->render('modules/my-job/afterhours.html.twig', $twig_data);
    }

    private function filterAfterhours(array $afterhours_all, string $type_filtered): array {

        return array_filter($afterhours_all, function ($afterhour) use ($type_filtered) {
            return $afterhour->getType() === $type_filtered;
        });

    }

    /**
     * @return array
     */
    private function getTimeToSpend(): array {
        $afterhours = [];

        $goals = $this->app->repositories->myJobAfterhoursRepository->getGoalsWithTime();

        foreach ($goals as $goal) {
            $time_remaining         = $goal[MyJobAfterhoursRepository::TIME_SUMMARY_FIELD];
            $goal_key               = (is_null($goal[MyJobAfterhoursRepository::GOAL_FIELD]) ? static::GENERAL_USAGE : $goal[MyJobAfterhoursRepository::GOAL_FIELD]);
            $afterhours[$goal_key]  = $time_remaining;
        }

        return $afterhours;
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function addFormDataToDB(Request $request): void {

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-job/afterhours/update/",name="my-job-afterhours-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myJobAfterhoursRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;

    }

    /**
     * @Route("/my-job/afterhours/remove/",name="my-job-afterhours-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_AFTERHOURS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    private function getForm() {
        $goalsWithTimes = $this->app->repositories->myJobAfterhoursRepository->getGoalsWithTime();
        $goals          = [];

        foreach ($goalsWithTimes as $goalWithTime) {
            $goals[] = $goalWithTime[MyJobAfterhoursRepository::GOAL_FIELD];
        }

        return $this->createForm(MyJobAfterhoursType::class, null, [
            'entity_enums' => $this->entity_enums,
            'goals'        => $goals,
        ]);
    }


}
