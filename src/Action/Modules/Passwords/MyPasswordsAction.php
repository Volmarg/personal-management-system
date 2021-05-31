<?php

namespace App\Action\Modules\Passwords;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyPasswordsAction
 * @package App\Action\Modules\Passwords
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PASSWORDS
 * )
 */
class MyPasswordsAction extends AbstractController {

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

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
        $passwordForm = $this->app->forms->myPasswordForm();
        $this->addFormDataToDB($passwordForm, $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getPasswordsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-passwords/remove/", name="my-passwords-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-passwords/update/" ,name="my-passwords-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = $parameters['id'];

        $entity   = $this->controller->getMyPasswordsController()->findPasswordEntityById($entityId);
        $response = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    protected function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $passwordForm  = $this->app->forms->myPasswordForm();
        $formView      = $passwordForm->createView();
        $passwords     = $this->controller->getMyPasswordsController()->findAllNotDeleted();
        $groups        = $this->controller->getMyPasswordsGroupsController()->findAllNotDeleted();

        return $this->render('modules/my-passwords/my-passwords.html.twig', [
            'form'                           => $formView,
            'ajax_render'                    => $ajaxRender,
            'passwords'                      => $passwords,
            'groups'                         => $groups,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getPasswordsPageTitle(),
        ]);

    }

    /**
     * @Route("/my-passwords/get-password/{id}" ,name="my-passwords-get-password")
     * @param $id
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getPasswordForId($id): JsonResponse
    {
        try {
            $encryptedPassword = $this->controller->getMyPasswordsController()->getPasswordForId($id);
            $decryptedPassword = $this->encryptor->decrypt($encryptedPassword);
            return AjaxResponse::buildJsonResponseForAjaxCall(200, "", null, $decryptedPassword);
        } catch (Exception $e) {
            $exceptionMessage = $e->getMessage();
            return AjaxResponse::buildJsonResponseForAjaxCall(500, $exceptionMessage);
        }
    }

    /**
     * @param $form
     * @param $request
     */
    protected function addFormDataToDB($form, $request) {
        $form->handleRequest($request);

        if ($form->isSubmitted($request) && $form->isValid()) {
            $formData = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($formData);
            $em->flush();
        }

    }

    /**
     * Will return passwords page title
     *
     * @return string
     */
    private function getPasswordsPageTitle(): string
    {
        return $this->app->translator->translate('passwords.title');
    }

}