<?php

namespace App\Action\Modules\Contacts;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Contacts\MyContactType;
use App\Services\Files\FilesHandler;
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

    const KEY_MESSAGE = "message";

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers = null;

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
    public function removeContactGroup(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_GROUP_REPOSITORY,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderSettingsTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-contacts-groups/update",name="my-contacts-groups-update")
     * @param Request $request
     * @return Response
     * 
     */
    public function updateContactGroup(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myContactGroupRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/my-contacts-types/remove", name="my-contacts-types-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeContactType(Request $request) {

        $record_id = $request->request->get('id');
        $response  = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_TYPE_REPOSITORY,
            $record_id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderSettingsTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-contacts-types/update",name="my-contacts-types-update")
     * @param Request $request
     * @return Response
     * 
     * @throws Exception
     */
    public function updateContactType(Request $request) {
        $parameters = $request->request->all();

        $entity_after_update  = $this->app->repositories->myContactTypeRepository->find($parameters['id']);
        $entity_before_update = clone($entity_after_update); // because doctrine will overwrite the data we got to clone it

        $this->app->em->beginTransaction(); //all or nothing
        {
            $response = $this->app->repositories->update($parameters, $entity_after_update);

            try{
                $this->controllers->getMyContactSettingsController()->updateContactsForUpdatedType($entity_before_update, $entity_after_update);
            }catch (Exception $e){
                $response = new Response("Could not update the contacts for updated contact type");
            }

        }
        $this->app->em->commit();

        return $response;
    }

    /**
     * @Route("/my-contacts-settings", name="my-contacts-settings")
     * @param Request $request
     * @return Response
     * 
     */
    public function displaySettingsPage(Request $request) {
        $this->submitContactTypeForm($request);
        $this->submitContactGroupForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        $template_content  = $this->renderSettingsTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderSettingsTemplate($ajax_render = false) {

        $type_form  = $this->app->forms->contactTypeForm();
        $group_form = $this->app->forms->contactGroupForm();

        $data = [
            'type_form'            => $type_form->createView(),
            'group_form'           => $group_form->createView(),
            'ajax_render'          => $ajax_render,
            'contact_types_table'  => $this->renderContactTypeTemplate()->getContent(),
            'contact_groups_table' => $this->renderContactGroupTemplate()->getContent()
        ];

        return $this->render(self::TWIG_TEMPLATE_SETTINGS, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderContactTypeTemplate($ajax_render = false) {

        $types = $this->app->repositories->myContactTypeRepository->findBy(['deleted' => 0]);

        $data = [
            'ajax_render' => $ajax_render,
            'types'       => $types,
        ];

        return $this->render(self::TWIG_TEMPLATE_CONTACT_TYPES_TABLE, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    private function renderContactGroupTemplate($ajax_render = false) {

        $groups = $this->app->repositories->myContactGroupRepository->findBy(['deleted' => 0]);

        $data = [
            'ajax_render' => $ajax_render,
            'groups'      => $groups,
        ];

        return $this->render(self::TWIG_TEMPLATE_CONTACT_GROUPS_TABLE, $data);
    }

    /**
     * @param Request $request
     * @return Response
     * 
     */
    private function submitContactTypeForm(Request $request):Response {
        $form = $this->app->forms->contactTypeForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var MyContactType $contact_type
             */
            $contact_type = $form->getData();
            $name         = $contact_type->getName();

            if (!is_null($contact_type) && $this->app->repositories->myContactTypeRepository->findBy([ 'name' => $name ] )) {
                $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
                return new JsonResponse($record_with_this_name_exist, 409);
            }

            $original_image_path = $contact_type->getImagePath();
            $image_path          = FilesHandler::addTrailingSlashIfMissing($original_image_path, true);
            $contact_type->setImagePath($image_path);

            $this->app->em->persist($contact_type);
            $this->app->em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($form_submitted_message, 200);
    }

    /**
     * @param Request $request
     * @return Response
     * 
     */
    private function submitContactGroupForm(Request $request):Response {
        $form = $this->app->forms->contactGroupForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var MyContactType $form_data
             */
            $form_data = $form->getData();
            $name      = $form_data->getName();

            if (!is_null($form_data) && $this->app->repositories->myContactGroupRepository->findBy([ 'name' => $name ] )) {
                $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
                return new Response($record_with_this_name_exist, 409);
            }

            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new Response($form_submitted_message, 200);
    }

}