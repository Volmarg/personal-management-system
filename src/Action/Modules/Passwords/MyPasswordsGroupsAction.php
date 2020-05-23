<?php

namespace App\Action\Modules\Passwords;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPasswordsGroupsAction extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-passwords-settings", name="my-passwords-settings")
     * @param Request $request
     * @return Response
     * 
     */
    public function display(Request $request) {
        $password_group_form = $this->app->forms->passwordGroupForm();
        $this->submitForm($password_group_form , $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-passwords-groups/remove", name="my-passwords-groups-remove")
     * @param Request $request
     * @return Response
     * 
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_GROUPS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-passwords-groups/update",name="my-passwords-groups-update")
     * @param Request $request
     * @return Response
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myPasswordsGroupsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $password_group_form = $this->app->forms->passwordGroupForm();
        $groups              = $this->app->repositories->myPasswordsGroupsRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-passwords/settings.html.twig', [
            'ajax_render'                    => $ajax_render,
            'groups'                         => $groups,
            'groups_form'                    => $password_group_form->createView(),
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return JsonResponse
     * 
     */
    private function submitForm(FormInterface $form, Request $request) {
        $form->handleRequest($request);
        /**
         * @var MyPasswordsGroups $form_data
         */
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myPasswordsGroupsRepository->findBy(['name' => $form_data->getName()])) {
            $record_with_this_name_exist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($record_with_this_name_exist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        $form_submitted_message = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($form_submitted_message, 200);
    }

}