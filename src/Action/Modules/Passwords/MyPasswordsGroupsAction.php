<?php

namespace App\Action\Modules\Passwords;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class MyPasswordsGroupsAction
 * @package App\Action\Modules\Passwords
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_PASSWORDS
 * )
 */
class MyPasswordsGroupsAction extends AbstractController {

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
     * @Route("/my-passwords-settings", name="my-passwords-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $passwordGroupForm = $this->app->forms->passwordGroupForm();
        $this->submitForm($passwordGroupForm , $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setPageTitle($this->getPasswordsSettingsPageTitle());
        $ajaxResponse->setCode(Response::HTTP_OK);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-passwords-groups/remove", name="my-passwords-groups-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_GROUPS_REPOSITORY_NAME,
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
     * @Route("/my-passwords-groups/update",name="my-passwords-groups-update")
     * @param Request $request
     * @return Response
     * @throws MappingException
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $entityId   = $parameters['id'];

        $entity     = $this->controllers->getMyPasswordsGroupsController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $passwordGroupForm = $this->app->forms->passwordGroupForm();
        $groups            = $this->controllers->getMyPasswordsGroupsController()->findAllNotDeleted();

        return $this->render('modules/my-passwords/settings.html.twig', [
            'ajax_render'                    => $ajaxRender,
            'groups'                         => $groups,
            'groups_form'                    => $passwordGroupForm->createView(),
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getPasswordsSettingsPageTitle(),
        ]);
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return JsonResponse
     * 
     */
    private function submitForm(FormInterface $form, Request $request): JsonResponse
    {
        $form->handleRequest($request);
        $formData = $form->getData();

        if (
                !is_null($formData)
            &&  !is_null($this->controllers->getMyPasswordsGroupsController()->findOneByName($formData->getName()))
        ) {
            $recordWithThisNameExist = $this->app->translator->translate('db.recordWithThisNameExist');
            return new JsonResponse($recordWithThisNameExist, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($formData);
            $this->app->em->flush();
        }

        $formSubmittedMessage = $this->app->translator->translate('forms.general.success');
        return new JsonResponse($formSubmittedMessage, 200);
    }

    /**
     * Will return passwords settings page title
     *
     * @return string
     */
    private function getPasswordsSettingsPageTitle(): string
    {
        return $this->app->translator->translate('passwords.settings.title');
    }

}