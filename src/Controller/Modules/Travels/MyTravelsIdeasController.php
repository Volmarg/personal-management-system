<?php

namespace App\Controller\Modules\Travels;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use App\Form\Modules\Travels\MyTravelsIdeasType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MyTravelsIdeasController extends AbstractController {

    static $MY_TRAVELS_IDEAS_ENTITY_NAME;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        static::$MY_TRAVELS_IDEAS_ENTITY_NAME = MyTravelsIdeas::class;
        $this->app = $app;
    }

    private function getForm() {
        $categories = $this->getAllCategories();
        return $this->createForm(MyTravelsIdeasType::class, null, ['categories' => $categories]);
    }

    /**
     * @Route("/my/travels/ideas", name="my-travels-ideas")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($this->getForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($this->getForm(), false);
        }
        return $this->renderTemplate($this->getForm(), true);
    }

    protected function renderTemplate($form, $ajax_render = false) {
        $form_view = $form->createView();

        $columns_names = $this->app->em->getClassMetadata(static::$MY_TRAVELS_IDEAS_ENTITY_NAME)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_ideas  = $this->app->repositories->myTravelsIdeasRepository->findBy(['deleted' => 0]);
        $categories = $this->getAllCategories();

        $data = [
            'form_view'     => $form_view,
            'columns_names' => $columns_names,
            'all_ideas'     => $all_ideas,
            'ajax_render'   => $ajax_render,
            'categories'    => $categories
        ];

        return $this->render('modules/my-travels/ideas.html.twig', $data);
    }

    /**
     * @param $form Form
     * @param $request
     * @return void
     */
    protected function addFormDataToDB($form, Request $request): void {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $request->request->get('my_travels_ideas');

            $idea = new MyTravelsIdeas();
            $idea->setCategory($form_data['category']);
            $idea->setCountry($form_data['country']);
            $idea->setLocation($form_data['location']);
            $idea->setImage($form_data['image']);
            $idea->setMap($form_data['map']);

            $this->app->em->persist($idea);
            $this->app->em->flush();

        }
    }

    /**
     * @Route("/my-travels/ideas/update/",name="my-travels-ideas-update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function remove(Request $request) {
        $id         = trim($request->request->get('id'));
        $response   = $this->app->repositories->deleteById(
            Repositories::MY_TRAVELS_IDEAS_REPOSITORY_NAME,
            $id
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($this->getForm(), true);
        }
        return $response;
    }

    private function getAllCategories(){
        return $this->app->repositories->myTravelsIdeasRepository->getAllCategories();
    }

}
