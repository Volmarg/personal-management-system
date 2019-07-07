<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\Modules\Notes\MyNotes;
use App\Form\Modules\Notes\MyNotesCategoriesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesCategoriesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-notes/settings", name="my-notes-settings")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function display(Request $request) {
        $response = $this->submitForm($this->getForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($this->getForm(), false);
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }
        return $this->renderTemplate($this->getForm(), true);
    }

    /**
     * @Route("/my-notes/settings/remove/", name="my-notes-settings-remove")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_NOTES_CATEGORIES_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($this->getForm(), true);
        }
        return $response;
    }

    /**
     * @Route("/my-notes/settings/update/",name="my-notes-settings-update")
     * @param Request $request
     * @return Response
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myNotesCategoriesRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param $form
     * @param bool $ajax_render
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    private function renderTemplate(FormInterface $form, $ajax_render = false) {

        $column_names = $this->getDoctrine()->getManager()->getClassMetadata(MyNotes::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $categories = $this->app->repositories->myNotesCategoriesRepository->findActiveCategories();

        return $this->render('modules/my-notes/settings.html.twig',
            [
                'ajax_render'   => $ajax_render,
                'categories'    => $categories,
                'column_names'  => $column_names,
                'form'          => $form->createView()
            ]
        );
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return JsonResponse
     */
    private function submitForm(FormInterface $form, Request $request) {
        $form->handleRequest($request);
        /**
         * @var MyNotesCategories $form_data
         */
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myNotesCategoriesRepository->findBy(['name' => $form_data->getName()])) {
            return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED,200);
    }

    private function getForm() {
        return $this->createForm(MyNotesCategoriesType::class);
    }
}
