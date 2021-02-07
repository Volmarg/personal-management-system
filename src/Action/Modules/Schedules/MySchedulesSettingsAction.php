<?php


namespace App\Action\Modules\Schedules;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MySchedulesSettingsAction extends AbstractController {

    const TWIG_TEMPLATE = 'modules/my-schedules/settings.html.twig';

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
     * @Route("/my-schedules-settings", name="my_schedules_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->addRecord($request);
        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $templateContent);
    }

    /**
     * @Route("/my-schedule-settings/schedule-type/remove", name="my_schedule_settings_schedule_remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_SCHEDULE_TYPE_REPOSITORY,
            $request->request->get('id')
        );

        $message = $response->getContent();
        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/my-schedule-settings/schedule-type/update",name="my_schedule_settings_schedule_update")
     * @param Request $request
     * @return Response
     *
     * @throws MappingException
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyScheduleTypeController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate($ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $form   = $this->app->forms->scheduleTypeForm();
        $types  = $this->controllers->getMyScheduleTypeController()->getAllNonDeletedTypes();

        return $this->render(self::TWIG_TEMPLATE,
            [
                'ajax_render'                    => $ajaxRender,
                'types'                          => $types,
                'form'                           => $form->createView(),
                'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 
     */
    private function addRecord(Request $request): JsonResponse
    {
        $form=$this->app->forms->scheduleTypeForm();
        $form->handleRequest($request);

        $formData = $form->getData();

        // info: i can make general util with this
        if (
                !is_null($formData)
            &&  !is_null($this->controllers->getMyScheduleTypeController()->findOneByName($formData->getName()))
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

}