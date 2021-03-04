<?php


namespace App\Action\Modules\Schedules;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Schedules\MyScheduleCalendar;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyScheduleCalendarAction
 * @package App\Action\Modules\Schedules
 */
class MyScheduleCalendarAction extends AbstractController {

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
     * @Route("/modules/schedules/calendar/update",name="schedules-calendar-update", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws MappingException
     * @throws Exception
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $id         = $parameters[self::PARAMETER_ID];

        $entity     = $this->controllers->getMyScheduleCalendarController()->findCalendarById($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        $message    = $response->getContent();
        $code       = $response->getStatusCode();

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * @Route("/modules/schedules/calendar/remove",name="schedules-calendar-remove", methods={"POST"})
     * @param Request $request
     * @param MySchedulesAction $mySchedulesAction
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request, MySchedulesAction $mySchedulesAction): Response
    {
        $id = trim($request->request->get(self::PARAMETER_ID));

        $response = $this->app->repositories->deleteById(Repositories::MY_SCHEDULE_CALENDAR_REPOSITORY, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $mySchedulesAction->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("/modules/schedules/calendar/create",name="schedules-calendar-create", methods={"POST"})
     * @param Request $request
     * @param MySchedulesAction $mySchedulesAction
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request, MySchedulesAction $mySchedulesAction): Response
    {
        $form = $this->app->forms->scheduleCalendarForm();
        $form->handleRequest($request);

        $formData = $form->getData();
        if( $formData instanceof MyScheduleCalendar ){
            // this is not a bug, TuiCalendar allows to handle multiple colors but it was planned so to just use one
            $formData->setBackgroundColor($formData->getColor());
            $formData->setBorderColor($formData->getColor());
            $formData->setDragBackgroundColor($formData->getColor());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($formData);
            $this->app->em->flush();

            $formSubmittedMessage = $this->app->translator->translate('messages.ajax.success.recordHasBeenCreated');
            $renderedTemplate     = $mySchedulesAction->renderTemplate(true, true); // todo: this first parameter will be gone later on

            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, $formSubmittedMessage, $renderedTemplate);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK);
    }

}