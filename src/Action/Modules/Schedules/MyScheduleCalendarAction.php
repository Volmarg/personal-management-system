<?php


namespace App\Action\Modules\Schedules;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Modules\Schedules\ScheduleCalendarDTO;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

/**
 * The logic inside this action is different that in other actions where everything relies on the DataLoaders in front
 * entities updates etc. Normally whenever something is added / changed then whole page is reloaded but in this case
 * there is fully interactive calendar with built in actions handling
 *
 * Class MyScheduleCalendarAction
 * @package App\Action\Modules\Schedules
 */
class MyScheduleCalendarAction extends AbstractController {

    const KEY_CALENDARS_DATA_JSONS = "calendarsDataJsons";

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
     * Will fetch all non deleted calendars in form of json
     *
     * @Route("/modules/schedules/calendar/get-all-non-deleted-calendars-data", methods={"GET"})
     */
    public function getAllNonDeletedCalendarsDataAsJson(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();

        try{
            $calendarsDataDtoArray   = $this->controllers->getMyScheduleCalendarController()->fetchAllNonDeletedCalendarsData();
            $calendarsDataJsonsArray = array_map( fn(ScheduleCalendarDTO $scheduleCalendarDto) => $scheduleCalendarDto->toJson(), $calendarsDataDtoArray);

            $ajaxResponse->setCode(Response::HTTP_OK);
            $ajaxResponse->setDataBag([
                self::KEY_CALENDARS_DATA_JSONS => $calendarsDataJsonsArray,
            ]);

        }catch(Exception | TypeError $e){
            $this->app->logExceptionWasThrown($e);
            $message = $this->app->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setMessage($message);

            return $ajaxResponse->buildJsonResponse();
        }


        return $ajaxResponse->buildJsonResponse();
    }

}