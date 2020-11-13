<?php

namespace App\Action\Modules\Passwords;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Passwords\MyPasswords;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPasswordsAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Controllers $controller
     */
    private Controllers $controller;

    public function __construct(Application $app, EncryptorInterface $encryptor, Controllers $controllers) {
        $this->app        = $app;
        $this->controller = $controllers;
        $this->encryptor  = $encryptor;
    }


    /**
     * @Route("/my-passwords", name="my-passwords")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {
        $password_form = $this->app->forms->myPasswordForm();
        $this->addFormDataToDB($password_form, $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-passwords/remove/", name="my-passwords-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-passwords/update/" ,name="my-passwords-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity_id  = $parameters['id'];

        $entity     = $this->controller->getMyPasswordsController()->findPasswordEntityById($entity_id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    protected function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $password_form  = $this->app->forms->myPasswordForm();
        $form_view      = $password_form->createView();
        $passwords      = $this->controller->getMyPasswordsController()->findAllNotDeleted();
        $groups         = $this->controller->getMyPasswordsGroupsController()->findAllNotDeleted();

        return $this->render('modules/my-passwords/my-passwords.html.twig', [
            'form'                           => $form_view,
            'ajax_render'                    => $ajax_render,
            'passwords'                      => $passwords,
            'groups'                         => $groups,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);

    }

    /**
     * @Route("/my-passwords/get-password/{id}" ,name="my-passwords-get-password")
     * @param $id
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getPasswordForId($id) {
        try {
            $encrypted_password = $this->controller->getMyPasswordsController()->getPasswordForId($id);
            $decrypted_password = $this->encryptor->decrypt($encrypted_password);
            return AjaxResponse::buildJsonResponseForAjaxCall(200, "", null, $decrypted_password);
        } catch (\Exception $e) {
            $exception_message = $e->getMessage();
            return AjaxResponse::buildJsonResponseForAjaxCall(500, $exception_message);
        }
    }

    /**
     * @param $form
     * @param $request
     */
    protected function addFormDataToDB($form, $request) {
        $form->handleRequest($request);

        if ($form->isSubmitted($request) && $form->isValid()) {

            /**
             * @var $form_data MyPasswords
             */
            $form_data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

    }

}