<?php

namespace App\Action\Modules\Notes;

use App\Controller\Modules\ModulesController;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\System\LockedResource;
use App\Repository\AbstractRepository;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyNotesAction
 * @package App\Action\Modules\Notes
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_NOTES
 * )
 */
class MyNotesAction extends AbstractController {

    const KEY_PARAMETER_ID = "id";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-notes/create", name="my-notes-create")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function createNote(Request $request): Response
    {
        $this->addToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCreateNoteTemplate();
        }

        $renderedTemplate = $this->renderCreateNoteTemplate(true);
        $templateContent  = $renderedTemplate->getContent();
        $ajaxResponse     = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getNoteCreatePageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-notes/category/{category}/{categoryId}", name="my-notes-category")
     * @param Request $request
     * @param $category
     * @param $categoryId
     * @return Response
     *
     * @throws Exception
     */
    public function openCategory(Request $request, $category, $categoryId): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderCategoryTemplate($category, $categoryId);
        }

        $templateContent = $this->renderCategoryTemplate($category, $categoryId, true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getNoteCategoryPageTitle($category));

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-notes/category/{category}/note/{noteName}", name="my-notes-note")
     * @param $noteName
     * @return Response
     */
    public function openNote(string $noteName): Response
    {

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
     * @throws MappingException
     */
    public function update(Request $request): Response
    {

        $parameters = $request->request->all();
        $id         = $parameters[AbstractRepository::FIELD_ID];
        $entity     = $this->controllers->getMyNotesController()->getOneById($id);

        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
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
            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @param string $category
     * @param string $categoryId
     * @param bool $ajaxRender
     * @return Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function renderCategoryTemplate(string $category, string $categoryId, bool $ajaxRender = false): Response
    {
        $requestedCategory = $this->controllers->getMyNotesCategoriesController()->findOneById($categoryId);
        if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($categoryId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_ENTITY_NOTES_CATEGORY) ){
            return $this->redirect('/');
        }

        if (!$requestedCategory || $category != $requestedCategory->getName()) {
            $message = $this->app->translator->translate('notes.category.error.categoryWithThisNameOrIdExist');
            $this->app->addDangerFlash($message);
            return $this->redirect($this->generateUrl('my-notes-create'));
        }

        $notes = $this->controllers->getMyNotesController()->getNotesByCategoriesIds([$categoryId]);
        foreach( $notes as $index => $note ){
            $noteId = $note->getId();
            if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($noteId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_NOTES, false) ){
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
            'category_id'   => $categoryId,
            'ajax_render'   => $ajaxRender,
            'notes'         => $notes,
            'page_title'    => $this->getNoteCategoryPageTitle($category),
        ]);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderCreateNoteTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response {

        $form     = $this->app->forms->noteTypeForm();
        $formView = $form->createView();

        $templateData = [
            'ajax_render'                    => $ajaxRender,
            'form'                           => $formView,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getNoteCreatePageTitle(),
        ];

        return $this->render('modules/my-notes/new-note.html.twig', $templateData);

    }

    /**
     * @param Request $request
     */
    private function addToDB(Request $request): void {
        $form = $this->app->forms->noteTypeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em       = $this->getDoctrine()->getManager();
            $formData = $form->getData();

            $em->persist($formData);
            $em->flush();
        }
    }

    /**
     * Will return note create page title
     *
     * @return string
     */
    private function getNoteCreatePageTitle(): string
    {
        return $this->app->translator->translate('notes.create.headers.title');
    }

    /**
     * Will return note create page title
     *
     * @param string $categoryName
     * @return string
     */
    private function getNoteCategoryPageTitle(string $categoryName): string
    {
        return $this->app->translator->translate(
            'notes.category.title',
            [
              "{{categoryName}}" => ucfirst($categoryName),
            ]
        );
    }
}