<?php


namespace App\Action\Modules\Schedules;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
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
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-schedules-settings", name="my_schedules_settings")
     * @param Request $request
     * @return Response
     * 
     */
    public function display(Request $request) {
        $this->addRecord($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-schedule-settings/schedule-type/remove", name="my_schedule_settings_schedule_remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_SCHEDULE_TYPE_REPOSITORY,
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
     * @Route("/my-schedule-settings/schedule-type/update",name="my_schedule_settings_schedule_update")
     * @param Request $request
     * @return Response
     * 
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myScheduleTypeRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderTemplate($ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form   = $this->app->forms->scheduleTypeForm();
        $types  = $this->app->repositories->myScheduleTypeRepository->findBy(['deleted' => 0]);

        return $this->render(self::TWIG_TEMPLATE,
            [
                'ajax_render'                    => $ajax_render,
                'types'                          => $types,
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
    private function addRecord(Request $request) {
        $form=$this->app->forms->scheduleTypeForm();
        $form->handleRequest($request);

        $form_data = $form->getData();

        // info: i can make general util with this
        if (!is_null($form_data) && $this->app->repositories->myScheduleTypeRepository->findBy(['name' => $form_data->getName()])) {
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