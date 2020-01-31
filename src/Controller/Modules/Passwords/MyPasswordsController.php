<?php

namespace App\Controller\Modules\Passwords;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Passwords\MyPasswords;
use App\Form\Modules\Passwords\MyPasswordsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;

class MyPasswordsController extends AbstractController {
    /**
     * @var Application $app
     */
    private $app;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(Application $app, EncryptorInterface $encryptor) {
        $this->app      = $app;
        $this->encryptor = $encryptor;
    }

    /**
     * @Route("/my-passwords", name="my-passwords")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {
        $password_form = $this->app->forms->myPasswordForm();
        $this->addFormDataToDB($password_form, $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-passwords/remove/", name="my-passwords-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-passwords/update/" ,name="my-passwords-update")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myPasswordsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    protected function renderTemplate($ajax_render = false) {

        $password_form  = $this->app->forms->myPasswordForm();
        $form_view      = $password_form->createView();
        $passwords      = $this->app->repositories->myPasswordsRepository->findBy(['deleted' => 0]);
        $groups         = $this->app->repositories->myPasswordsGroupsRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-passwords/my-passwords.html.twig', [
            'form'          => $form_view,
            'ajax_render'   => $ajax_render,
            'passwords'     => $passwords,
            'groups'        => $groups
        ]);

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

    /**
     * @Route("/my-passwords/get-password/{id}" ,name="my-passwords-get-password")
     * @param $id
     * @return JsonResponse
     */
    public function getPasswordForId($id) {
        try {
            $encrypted_password = $this->app->repositories->myPasswordsRepository->getPasswordForId($id);
            $decrypted_password = $this->encryptor->decrypt($encrypted_password);
            return AjaxResponse::buildResponseForAjaxCall(200, "", null, $decrypted_password);
        } catch (\Exception $e) {
            $exception_message = $e->getMessage();
            return AjaxResponse::buildResponseForAjaxCall(500, $exception_message);
        }
    }

}
