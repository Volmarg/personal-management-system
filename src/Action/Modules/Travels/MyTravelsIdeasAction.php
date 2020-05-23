<?php

namespace App\Action\Modules\Travels;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyTravelsIdeasAction extends AbstractController {

    /**
     * @var Application $app
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

    private function getForm() {
        $categories = $this->controllers->getMyTravelsIdeasController()->getAllCategories();
        $travel_ideas_form = $this->app->forms->travelIdeasForm(['categories' => $categories]);
        return $travel_ideas_form;
    }

    /**
     * @Route("/my-travels/ideas", name="my-travels-ideas")
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

    protected function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $form      = $this->getForm();
        $form_view = $form->createView();

        $columns_names = $this->app->em->getClassMetadata(MyTravelsIdeas::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_ideas  = $this->app->repositories->myTravelsIdeasRepository->findBy(['deleted' => 0]);
        $categories = $this->controllers->getMyTravelsIdeasController()->getAllCategories();

        $data = [
            'form_view'     => $form_view,
            'columns_names' => $columns_names,
            'all_ideas'     => $all_ideas,
            'ajax_render'   => $ajax_render,
            'categories'    => $categories,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-travels/ideas.html.twig', $data);
    }

    /**
     * @param $request
     * @return void
     */
    protected function addFormDataToDB(Request $request): void {

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $travel_idea = $form->getData();

            $this->app->em->persist($travel_idea);
            $this->app->em->flush();
        }
    }

    /**
     * @Route("/my-travels/ideas/update/",name="my-travels-ideas-update")
     * @param Request $request
     * @return Response
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myTravelsIdeasRepository->find($parameters['id']);

        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-travels/ideas/remove/",name="my-travels-ideas-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {
        $id         = trim($request->request->get('id'));
        $response   = $this->app->repositories->deleteById(
            Repositories::MY_TRAVELS_IDEAS_REPOSITORY_NAME,
            $id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

}