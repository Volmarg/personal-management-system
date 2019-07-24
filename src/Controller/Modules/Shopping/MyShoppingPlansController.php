<?php

namespace App\Controller\Modules\Shopping;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Form\Modules\Shopping\MyShoppingPlansType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Symfony\Component\HttpFoundation\Response;

class MyShoppingPlansController extends AbstractController {

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
        $plans_form_view    = $form->createView();
        $columns_names      = $this->getDoctrine()->getManager()->getClassMetadata(MyShoppingPlans::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_plans = $this->app->repositories->myShoppingPlansRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-shopping/plans.html.twig', compact(
            'plans_form_view', 'columns_names', 'all_plans', 'ajax_render'
        ));
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
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

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($this->getForm(), true);
        }
        return $response;
    }

    private function getForm() {
        return $this->createForm(MyShoppingPlansType::class);

    }

}
