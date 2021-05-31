<?php

namespace App\Action\Modules\Contacts;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Services\Files\FilesHandler;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsSettingsAction extends AbstractController
{

    const TWIG_TEMPLATE_SETTINGS             = 'modules/my-contacts/settings.html.twig';
    const TWIG_TEMPLATE_CONTACT_TYPES_TABLE  = 'modules/my-contacts/components/settings/types-settings.table.html.twig';
    const TWIG_TEMPLATE_CONTACT_GROUPS_TABLE = 'modules/my-contacts/components/settings/groups-settings.table.html.twig';

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
     * @Route("/my-contacts-groups/remove", name="my-contacts-groups-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeContactGroup(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_GROUP_REPOSITORY,
            $request->request->get('id')
        );

        $message = $response->getContent();
        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderSettingsTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-contacts-groups/update",name="my-contacts-groups-update")
     * @param Request $request
     * @return Response
     *
     * @throws MappingException
     */
    public function updateContactGroup(Request $request): Response
    {
        $parameters = $request->request->all();
        $entityId   = $parameters['id'];

        $entity     = $this->controllers->getMyContactGroupController()->getOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-contacts-types/remove", name="my-contacts-types-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeContactType(Request $request): Response
    {
        $recordId = $request->request->get('id');
        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_TYPE_REPOSITORY,
            $recordId
        );

        $message = $response->getContent();
        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderSettingsTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-contacts-types/update",name="my-contacts-types-update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function updateContactType(Request $request): Response
    {
        $parameters = $request->request->all();
        $id         = trim($parameters['id']);
        $entityAfterUpdate  = $this->controllers->getMyContactTypeController()->findOneById($id);
        $entityBeforeUpdate = clone($entityAfterUpdate); // because doctrine will overwrite the data we got to clone it

        $this->app->em->beginTransaction(); //all or nothing
        {
            $response = $this->app->repositories->update($parameters, $entityAfterUpdate);

            try{
                $this->controllers->getMyContactSettingsController()->updateContactsForUpdatedType($entityBeforeUpdate, $entityAfterUpdate);
            }catch (Exception $e){
                $response = new Response("Could not update the contacts for updated contact type", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        }
        $this->app->em->commit();

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-contacts-settings", name="my-contacts-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettingsPage(Request $request): Response
    {
        $this->submitContactTypeForm($request);
        $this->submitContactGroupForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate();
        }

        $templateContent = $this->renderSettingsTemplate(true)->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setPageTitle($this->getContactsSettingsPageTitle());
        $ajaxResponse->setCode(Response::HTTP_OK);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderSettingsTemplate($ajaxRender = false): Response
    {

        $typeForm  = $this->app->forms->contactTypeForm();
        $groupForm = $this->app->forms->contactGroupForm();

        $data = [
            'type_form'            => $typeForm->createView(),
            'group_form'           => $groupForm->createView(),
            'ajax_render'          => $ajaxRender,
            'contact_types_table'  => $this->renderContactTypeTemplate()->getContent(),
            'contact_groups_table' => $this->renderContactGroupTemplate()->getContent(),
            'page_title'           => $this->getContactsSettingsPageTitle(),
        ];

        return $this->render(self::TWIG_TEMPLATE_SETTINGS, $data);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderContactTypeTemplate($ajaxRender = false): Response
    {

        $types = $this->controllers->getMyContactTypeController()->getAllNotDeleted();

        $data = [
            'ajax_render' => $ajaxRender,
            'types'       => $types,
        ];

        return $this->render(self::TWIG_TEMPLATE_CONTACT_TYPES_TABLE, $data);
    }

    /**
     * @param bool $ajaxRender
     * @return Response
     */
    private function renderContactGroupTemplate($ajaxRender = false): Response
    {

        $groups = $this->controllers->getMyContactGroupController()->getAllNotDeleted();

        $data = [
            'ajax_render' => $ajaxRender,
            'groups'      => $groups,
        ];

        return $this->render(self::TWIG_TEMPLATE_CONTACT_GROUPS_TABLE, $data);
    }

    /**
     * @param Request $request
     * @return Response
     * 
     */
    private function submitContactTypeForm(Request $request): Response
    {
        $form = $this->app->forms->contactTypeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contactType = $form->getData();
            $name        = $contactType->getName();

            if (
                        !is_null($contactType)
                    &&  !is_null($this->controllers->getMyContactTypeController()->getOneByName($name))
            ) {
                $recordWithThisNameExist = $this->app->translator->translate('db.recordWithThisNameExist');
                return new JsonResponse($recordWithThisNameExist, 409);
            }

            $originalImagePath = $contactType->getImagePath();
            $imagePath         = FilesHandler::addTrailingSlashIfMissing($originalImagePath, true);
            $contactType->setImagePath($imagePath);

            $this->app->em->persist($contactType);
            $this->app->em->flush();
        }

        $formSubmittedMessage = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($formSubmittedMessage, 200);
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function submitContactGroupForm(Request $request): Response
    {
        $form = $this->app->forms->contactGroupForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData        = $form->getData();
            $name            = $formData->getName();
            $normalizedColor = str_replace("#", "", $formData->getColor());

            $formData->setColor($normalizedColor);

            if (
                        !is_null($formData)
                    &&  $this->controllers->getMyContactGroupController()->getOneByName($name)
            ) {
                $recordWithThisNameExist = $this->app->translator->translate('db.recordWithThisNameExist');
                return new Response($recordWithThisNameExist, 409);
            }

            $this->app->em->persist($formData);
            $this->app->em->flush();
        }

        $formSubmittedMessage = $this->app->translator->translate('forms.general.success');
        return new Response($formSubmittedMessage, 200);
    }

    /**
     * Will return contacts settings page title
     *
     * @return string
     */
    private function getContactsSettingsPageTitle(): string
    {
        return $this->app->translator->translate('contact.settings.title');
    }

}