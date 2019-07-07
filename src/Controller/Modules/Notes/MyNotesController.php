<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Form\Modules\Notes\MyNotesType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-notes/create", name="my-notes-create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createNote(Request $request) {
        $form_view = $this->getForm()->createView();
        $this->addToDB($this->getForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->render('modules/my-notes/new-note.html.twig',
                [
                    'ajax_render' => false,
                    'form'        => $form_view
                ]
            );
        }

        return $this->render('modules/my-notes/new-note.html.twig',
            [
                'ajax_render'   => true,
                'form'          => $form_view
            ]
        );
    }

    /**
     * @Route("/my-notes/category/{category}/{category_id}", name="my-notes-category")
     * @param Request $request
     * @param $category
     * @param $category_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openCategory(Request $request, $category, $category_id) {

        /**
         * @var MyNotesCategories $requested_category
         */
        $requested_category = $this->app->repositories->myNotesCategoriesRepository->find($category_id);

        if (!$requested_category || $category != $requested_category->getName()) {
            $this->addFlash('danger', GeneralMessagesController::CATEGORY_EXISTS);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        $notes = $this->app->repositories->myNotesRepository->getNotesByCategory($category_id);

        if (empty($notes)) {
            $this->addFlash('danger', GeneralMessagesController::CATEGORY_EMPTY_REDIRECT);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        return $this->render('modules/my-notes/category.html.twig', [
            'category'      => $category,
            'category_id'   => $category_id,
            'ajax_render'   => false,
            'notes'         => $notes
        ]);
    }

    /**
     * @Route("/my-notes/category/{category}/note/{noteName}", name="my-notes-note")
     * @param $noteName
     * @return Response
     */
    public function openNote(string $noteName) {

        return $this->render('modules/my-notes/note-details.html.twig', [
            'ajax_render'   => false,
            'note'          => $noteName
        ]);
    }

    /**
     * @Route("/my-notes/update/", name="my-notes-update")
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response {

        $parameters         = $request->request->all();
        $entity             = $this->app->repositories->myNotesRepository->find($parameters['id']);

        $response           = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-notes/delete-note/", name="my-notes-delete-note")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function deleteNote(Request $request): Response {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_NOTES_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return new Response('');

        }
        return $response;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     */
    private function addToDB(FormInterface $form, Request $request): void {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    private function getForm() {

        return $this->createForm(MyNotesType::class);
    }

}
