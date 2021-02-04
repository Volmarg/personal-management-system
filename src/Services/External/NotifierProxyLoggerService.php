<?php

namespace App\Services\External;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\DTO\Discord\DiscordMessageDTO;
use App\DTO\Mail\MailDTO;
use App\Entity\Modules\Schedules\MySchedule;
use App\NotifierProxyLoggerBridge;
use App\Request\Discord\InsertDiscordMessageRequest;
use App\Request\Mail\InsertMailRequest;
use App\Response\Discord\InsertDiscordMessageResponse;
use App\Response\Mail\InsertMailResponse;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Handles communication with NPL
 *
 * Class NotifierProxyLoggerService
 * @package App\Services\External
 */
class NotifierProxyLoggerService
{

    /**
     * @var NotifierProxyLoggerBridge $notifier_proxy_logger_bridge
     */
    private NotifierProxyLoggerBridge $notifier_proxy_logger_bridge;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * NotifierProxyLoggerService constructor.
     *
     * @param NotifierProxyLoggerBridge $notifier_proxy_logger_bridge
     * @param Application $app
     */
    public function __construct(NotifierProxyLoggerBridge $notifier_proxy_logger_bridge, Application $app)
    {
        $this->app                          = $app;
        $this->notifier_proxy_logger_bridge = $notifier_proxy_logger_bridge;
    }

    /**
     * Will use schedule and insert single discord message to the queue in NPL
     *
     * @param MySchedule $schedule
     * @return InsertDiscordMessageResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function insertDiscordMessageForSchedule(MySchedule $schedule): InsertDiscordMessageResponse
    {
        try{
            $discord_message_dto = new DiscordMessageDTO();
            $request             = new InsertDiscordMessageRequest();

            $discord_message_dto->setWebhookName(NotifierProxyLoggerBridge::WEBHOOK_NAME_ALL_NOTIFICATIONS);
            $discord_message_dto->setMessageTitle($schedule->getName());
            $discord_message_dto->setMessageContent($schedule->getInformation());
            $discord_message_dto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);

            $request->setDiscordMessageDto($discord_message_dto);
            $response = $this->notifier_proxy_logger_bridge->insertDiscordMessage($request);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will use schedule and insert single email to the queue in NPL
     *
     * @param MySchedule $schedule
     * @return InsertMailResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function insertEmailForSchedule(MySchedule $schedule): InsertMailResponse
    {
        try{
            $mail_dto = new MailDTO();
            $request  = new InsertMailRequest();

            $mail_dto->setToEmails(Env::getNotifierProxyLoggerDefaultReceiversEmails());
            $mail_dto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);
            $mail_dto->setFromEmail($this->app->config_loaders->getConfigLoaderSystem()->getSystemFromEmail());
            $mail_dto->setSubject($schedule->getName());
            $mail_dto->setBody($schedule->getInformation());

            $request->setMailDto($mail_dto);
            $response = $this->notifier_proxy_logger_bridge->insertMail($request);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }
}