<?php

namespace App\Action\Modules\Job;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Job\MyJobAfterhours;
use App\Form\Modules\Job\MyJobAfterhoursType;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MyJobAfterhoursAction extends AbstractController {

    /**
     * @var array $entity_enums
     */
    private $entity_enums = [];

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $entity_enums       = [MyJobAfterhours::TYPE_MADE, MyJobAfterhours::TYPE_SPENT];

        $this->entity_enums = array_combine(
            array_map('ucfirst', array_values($entity_enums)),
            $entity_enums
        );

        $this->app         = $app;
        $this->controllers = $controllers;
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

        $template_content  = $this->renderTemplate(true, false)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form                 = $this->getForm();
        $afterhours_form_view = $form->createView();

        $column_names       = $this->getDoctrine()->getManager()->getClassMetadata(MyJobAfterhours::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $afterhours_all     = $this->app->repositories->myJobAfterhoursRepository->findBy(['deleted' => 0]);
        $afterhours_spent   = $this->filterAfterhours($afterhours_all, MyJobAfterhours::TYPE_SPENT);
        $afterhours_made    = $this->filterAfterhours($afterhours_all, MyJobAfterhours::TYPE_MADE);

        $remaining_time_to_spend_per_goal = $this->controllers->getMyJobAfterhoursController()->getTimeToSpend();

        $twig_data = [
            'afterhours_form_view'              => $afterhours_form_view,
            'column_names'                      => $column_names,
            'afterhours_all'                    => $afterhours_all,
            'afterhours_spent'                  => $afterhours_spent,
            'afterhours_made'                   => $afterhours_made,
            'remaining_time_to_spend_per_goal'  => $remaining_time_to_spend_per_goal,
            'ajax_render'                       => $ajax_render,
            'skip_rewriting_twig_vars_to_js'    => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-job/afterhours.html.twig', $twig_data);
    }


    /**
     * @Route("/my-job/afterhours/update/",name="my-job-afterhours-update")
     * @param Request $request
     * @return JsonResponse
     * 
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
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @return FormInterface
     */
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

    /**
     * @param array $afterhours_all
     * @param string $type_filtered
     * @return array
     */
    private function filterAfterhours(array $afterhours_all, string $type_filtered): array {

        return array_filter($afterhours_all, function ($afterhour) use ($type_filtered) {
            return $afterhour->getType() === $type_filtered;
        });

    }

    /**
     * @param Request $request
     * @return void
     */
    private function addFormDataToDB(Request $request): void {

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

}
