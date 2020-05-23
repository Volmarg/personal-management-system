<?php

namespace App\Action\Modules\Shopping;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyShoppingPlansAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-shopping/plans", name="my-shopping-plans")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $shopping_plan_form = $this->app->forms->myShoppingPlanForm();
        $this->addFormDataToDB($shopping_plan_form, $request);

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
    protected function renderTemplate($ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $form               = $this->app->forms->myShoppingPlanForm();
        $plans_form_view    = $form->createView();
        $columns_names      = $this->getDoctrine()->getManager()->getClassMetadata(MyShoppingPlans::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_plans = $this->app->repositories->myShoppingPlansRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-shopping/plans.html.twig', [
            'plans_form_view'                => $plans_form_view,
            'columns_names'                  => $columns_names,
            'all_plans'                      => $all_plans,
            'ajax_render'                    => $ajax_render,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param $plans_form
     * @param Request $request
     */
    protected function addFormDataToDB($plans_form, Request $request): void {
        $plans_form->handleRequest($request);

        if ($plans_form->isSubmitted() && $plans_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($plans_form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-shopping/plans/update/",name="my-shopping-plans-update")
     * @param Request $request
     * @return JsonResponse|Response
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myShoppingPlansRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-shopping/plans/remove/",name="my-shopping-plans-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {
        $id         = $request->request->get('id');

        $response   = $this->app->repositories->deleteById(
            Repositories::MY_SHOPPING_PLANS_REPOSITORY_NAME,
            $id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

}