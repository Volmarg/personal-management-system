<?php

namespace App\Action\Modules\Goals;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoalsListAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("/admin/subgoals/update/",name="my-goals-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function updateSubgoal(Request $request) {
        $parameters = $request->request->all();
        $subgoal_id = $parameters['id'];
        $goal_id    = $parameters['myGoal']['id'];
        $entity     = $this->app->repositories->myGoalsSubgoalsRepository->find($subgoal_id);
        $response   = $this->app->repositories->update($parameters, $entity);

        $are_all_goals_done = boolval($this->app->repositories->myGoalsRepository->areAllSubgoalsDone($goal_id));

        if (empty($goal_id)) {
            return $response;
        }

        $goal = $this->app->repositories->myGoalsRepository->find($goal_id);

        if($are_all_goals_done){
            $goal->setCompleted(true);
        }else{
            $goal->setCompleted(false);
        }

        $this->app->em->persist($goal);
        $this->app->em->flush();

        return $response;

    }

    /**
     * @Route("admin/goals/list", name="goals_list")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_goals      = $this->app->repositories->myGoalsRepository->findBy(['deleted' => 0]);
        $all_subgoals   = $this->app->repositories->myGoalsSubgoalsRepository->findBy(['deleted' => 0]);

        $data = [
            'all_goals'                      => $all_goals,
            'all_subgoals'                   => $all_subgoals,
            'ajax_render'                    => $ajax_render,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-goals/list.html.twig',$data);
    }

}