<?php

namespace App\Action\Modules\Notes;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Notes\MyNotes;
use App\Entity\Modules\Notes\MyNotesCategories;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyNotesCategoriesAction
 * @package App\Action\Modules\Notes
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_NOTES
 * )
 */
class MyNotesCategoriesAction extends AbstractController {

    const PARAMETER_ID = "id";

    /**
     * @var Application
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
     * @Route("/my-notes/settings", name="my-notes-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     * 
     */
    public function display(Request $request): Response
    {
        $jsonResponse = $this->submitForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $message          = $jsonResponse->getContent();
        $code             = $jsonResponse->getStatusCode();
        $templateContent  = $this->renderTemplate(true)->getContent();

        $ajaxResponse     = new AjaxResponse($message, $templateContent);
        $ajaxResponse->setCode($code);
        $ajaxResponse->setPageTitle($this->getNotesSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-notes/settings/remove/", name="my-notes-settings-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     * 
     */
    public function remove(Request $request): Response
    {

        $id = $request->request->get(self::PARAMETER_ID);

        $response = $this->app->repositories->deleteById(Repositories::MY_NOTES_CATEGORIES_REPOSITORY_NAME, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-notes/settings/update/",name="my-notes-settings-update")
     * @param Request $request
     * @return Response
     * @throws MappingException
     * @throws Exception
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $id         = trim($parameters[self::PARAMETER_ID]);

        $entity     = $this->controllers->getMyNotesCategoriesController()->findOneById($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        $message    = $response->getContent();
        $code       = $response->getStatusCode();

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $form        = $this->app->forms->noteCategoryForm();
        $columnNames = $this->getDoctrine()->getManager()->getClassMetadata(MyNotes::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columnNames);

        $categories          = $this->controllers->getMyNotesCategoriesController()->findAllNotDeleted();
        $parentsChildrenDtos = $this->controllers->getMyNotesCategoriesController()->buildParentsChildrenCategoriesHierarchy();

        return $this->render('modules/my-notes/settings.html.twig',
            [
                'ajax_render'                    => $ajaxRender,
                'categories'                     => $categories,
                'parents_children_dtos'          => $parentsChildrenDtos,
                'column_names'                   => $columnNames,
                'form'                           => $form->createView(),
                'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
                'page_title'                     => $this->getNotesSettingsPageTitle(),
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
         * @var MyNotesCategories $formData
         */
        $formData = $form->getData();

        if( $formData instanceof MyNotesCategories ){
            $parentId = $formData->getParentId();
            $name     = $formData->getName();

            $categoryHasChildWithThisName = $this->controllers->getMyNotesCategoriesController()->hasCategoryChildWithThisName($name, $parentId);
            if ($categoryHasChildWithThisName) {
                $message = $this->app->translator->translate('notes.category.error.categoryWithThisNameAlreadyExistsInThisParent');
                return new JsonResponse($message, Response::HTTP_CONFLICT);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($formData);
            $this->app->em->flush();

            $formSubmittedMessage = $this->app->translator->translate('messages.ajax.success.recordHasBeenCreated');
            return new JsonResponse($formSubmittedMessage,Response::HTTP_OK);
        }

        return new JsonResponse("",Response::HTTP_OK);
    }

    /**
     * Will return notes settings page title
     *
     * @return string
     */
    private function getNotesSettingsPageTitle(): string
    {
        return $this->app->translator->translate('notes.settings.title');
    }

}