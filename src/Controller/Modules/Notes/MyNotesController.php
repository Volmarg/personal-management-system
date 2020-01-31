<?php

namespace App\Controller\Modules\Notes;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Notes\MyNotesCategories;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesController extends AbstractController {

    const KEY_CATEGORY_ID   = 'category_id';
    const KEY_CATEGORY_NAME = "category_name";

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
     * @return Response
     */
    public function createNote(Request $request) {
        $this->addToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCreateNoteTemplate(false);
        }

        $rendered_template = $this->renderCreateNoteTemplate(true);
        $template_content  = $rendered_template->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-notes/category/{category}/{category_id}", name="my-notes-category")
     * @param Request $request
     * @param $category
     * @param $category_id
     * @return Response
     */
    public function openCategory(Request $request, $category, $category_id) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($category, $category_id, false);
        }

        $template_content  = $this->renderCategoryTemplate($category, $category_id, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param string $category
     * @param string $category_id
     * @param bool $ajax_render
     * @return RedirectResponse|Response
     */
    protected function renderCategoryTemplate(string $category, string $category_id, bool $ajax_render = false) {

        /**
         * @var MyNotesCategories $requested_category
         */
        $requested_category = $this->app->repositories->myNotesCategoriesRepository->find($category_id);

        if (!$requested_category || $category != $requested_category->getName()) {
            $message = $this->app->translator->translate('notes.category.error.categoryWithThisNameOrIdExist');
            $this->addFlash('danger', $message);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        $notes = $this->app->repositories->myNotesRepository->getNotesByCategory($category_id);

        if (empty($notes)) {
            $message = $this->app->translator->translate('notes.category.error.categoryIsEmpty');
            $this->addFlash('danger', $message);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        return $this->render('modules/my-notes/category.html.twig', [
            'category'      => $category,
            'category_id'   => $category_id,
            'ajax_render'   => $ajax_render,
            'notes'         => $notes,
        ]);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderCreateNoteTemplate(bool $ajax_render): Response {

        $form       = $this->app->forms->noteTypeForm();
        $form_view  = $form->createView();

        $template_data = [
            'ajax_render'   => $ajax_render,
            'form'          => $form_view
        ];

        return $this->render('modules/my-notes/new-note.html.twig', $template_data);

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

        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myNotesRepository->find($parameters['id']);

        $response   = $this->app->repositories->update($parameters, $entity);

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

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildResponseForAjaxCall(200, $message);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @param Request $request
     */
    private function addToDB(Request $request): void {
        $form = $this->app->forms->noteTypeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

}
