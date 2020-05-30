<?php

namespace App\Action\Modules\Notes;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyNotesCategoriesAction extends AbstractController {

    const PARAMETER_ID = "id";

    /**
     * @var Application
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

    /**
     * @Route("/my-notes/settings", name="my-notes-settings")
     * @param Request $request
     * @return Response
     * @throws DBALException
     * 
     */
    public function display(Request $request) {
        $json_response = $this->submitForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $message           = $json_response->getContent();
        $code              = $json_response->getStatusCode();
        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall($code, $message, $template_content);
    }

    /**
     * @Route("/my-notes/settings/remove/", name="my-notes-settings-remove")
     * @param Request $request
     * @return Response
     * @throws DBALException
     * 
     */
    public function remove(Request $request) {

        $id = $request->request->get(self::PARAMETER_ID);

        $response = $this->app->repositories->deleteById(Repositories::MY_NOTES_CATEGORIES_REPOSITORY_NAME, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-notes/settings/update/",name="my-notes-settings-update")
     * @param Request $request
     * @return Response
     * @throws MappingException
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $id         = $parameters[self::PARAMETER_ID];

        $entity     = $this->app->repositories->myNotesCategoriesRepository->find($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        $message    = $response->getContent();
        $code       = $response->getStatusCode();

        return AjaxResponse::buildResponseForAjaxCall($code, $message);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form         = $this->app->forms->noteCategoryForm();
        $column_names = $this->getDoctrine()->getManager()->getClassMetadata(MyNotes::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($column_names);

        $categories            = $this->app->repositories->myNotesCategoriesRepository->findAllNotDeleted();
        $parents_children_dtos = $this->controllers->getMyNotesCategoriesController()->buildParentsChildrenCategoriesHierarchy();

        return $this->render('modules/my-notes/settings.html.twig',
            [
                'ajax_render'                    => $ajax_render,
                'categories'                     => $categories,
                'parents_children_dtos'          => $parents_children_dtos,
                'column_names'                   => $column_names,
                'form'                           => $form->createView(),
                'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 
     */
    private function submitForm(Request $request) {
        $form = $this->app->forms->noteCategoryForm();
        $form->handleRequest($request);
        /**
         * @var MyNotesCategories $form_data
         */
        $form_data = $form->getData();

        if( $form_data instanceof MyNotesCategories ){
            $parent_id = $form_data->getParentId();
            $name      = $form_data->getName();

            $category_has_child_with_this_name = $this->controllers->getMyNotesCategoriesController()->hasCategoryChildWithThisName($name, $parent_id);

            if ($category_has_child_with_this_name) {
                $message = $this->app->translator->translate('notes.category.error.categoryWithThisNameAlreadyExistsInThisParent');
                return new JsonResponse($message, Response::HTTP_CONFLICT);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();

            $form_submitted_message = $this->app->translator->translate('messages.ajax.success.recordHasBeenCreated');
            return new JsonResponse($form_submitted_message,Response::HTTP_OK);
        }

        return new JsonResponse("",Response::HTTP_OK);
    }

}