<?php

namespace App\Action\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Notes\MyNotesCategories;
use App\Entity\System\LockedResource;
use App\Repository\AbstractRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesAction extends AbstractController {

    const KEY_PARAMETER_ID = "id";

    /**
     * @var Application $app
     */
    private $app = null;

    /**
     * @var Controllers $controllers
     */
    private $controllers = null;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
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
     * 
     */
    public function openCategory(Request $request, $category, $category_id) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($category, $category_id, false);
        }

        $template_content  = $this->renderCategoryTemplate($category, $category_id, true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
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
     * 
     */
    public function update(Request $request): Response {

        $parameters = $request->request->all();
        $id         = $parameters[AbstractRepository::FIELD_ID];
        $entity     = $this->app->repositories->myNotesRepository->find($id);

        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-notes/delete-note/", name="my-notes-delete-note")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function deleteNote(Request $request): Response {

        $id       = $request->request->get(self::KEY_PARAMETER_ID);
        $response = $this->app->repositories->deleteById(Repositories::MY_NOTES_REPOSITORY_NAME, $id);

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            return AjaxResponse::buildResponseForAjaxCall(200, $message);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @param string $category
     * @param string $category_id
     * @param bool $ajax_render
     * @return Response
     * 
     */
    private function renderCategoryTemplate(string $category, string $category_id, bool $ajax_render = false) {

        /**
         * @var MyNotesCategories $requested_category
         */
        $requested_category = $this->app->repositories->myNotesCategoriesRepository->find($category_id);

        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($category_id, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY) ){
            return $this->redirect('/');
        }

        if (!$requested_category || $category != $requested_category->getName()) {
            $message = $this->app->translator->translate('notes.category.error.categoryWithThisNameOrIdExist');
            $this->app->addDangerFlash($message);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        $notes = $this->app->repositories->myNotesRepository->getNotesByCategory([$category_id]);

        foreach( $notes as $index => $note ){
            $note_id = $note->getId();
            if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($note_id, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES, false) ){
                unset($notes[$index]);
            }
        }

        if (empty($notes)) {
            $message = $this->app->translator->translate('notes.category.error.categoryIsEmpty');
            $this->app->addDangerFlash($message);
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
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderCreateNoteTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false): Response {

        $form       = $this->app->forms->noteTypeForm();
        $form_view  = $form->createView();

        $template_data = [
            'ajax_render'                    => $ajax_render,
            'form'                           => $form_view,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-notes/new-note.html.twig', $template_data);

    }

    /**
     * @param Request $request
     */
    private function addToDB(Request $request): void {
        $form = $this->app->forms->noteTypeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em        = $this->getDoctrine()->getManager();
            $form_data = $form->getData();

            $em->persist($form_data);
            $em->flush();
        }
    }

}