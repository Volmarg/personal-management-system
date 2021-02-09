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
     * @var NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     */
    private NotifierProxyLoggerBridge $notifierProxyLoggerBridge;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * NotifierProxyLoggerService constructor.
     *
     * @param NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     * @param Application $app
     */
    public function __construct(NotifierProxyLoggerBridge $notifierProxyLoggerBridge, Application $app)
    {
        $this->app                       = $app;
        $this->notifierProxyLoggerBridge = $notifierProxyLoggerBridge;
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
            $discordMessageDto = new DiscordMessageDTO();
            $request           = new InsertDiscordMessageRequest();

            $discordMessageDto->setWebhookName(NotifierProxyLoggerBridge::WEBHOOK_NAME_ALL_NOTIFICATIONS);
            $discordMessageDto->setMessageTitle($schedule->getName());
            $discordMessageDto->setMessageContent($schedule->getInformation());
            $discordMessageDto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);

            $request->setDiscordMessageDto($discordMessageDto);
            $response = $this->notifierProxyLoggerBridge->insertDiscordMessage($request);
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
            $mailDto = new MailDTO();
            $request = new InsertMailRequest();

            $mailDto->setToEmails(Env::getNotifierProxyLoggerDefaultReceiversEmails());
            $mailDto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);
            $mailDto->setFromEmail($this->app->configLoaders->getConfigLoaderSystem()->getSystemFromEmail());
            $mailDto->setSubject($schedule->getName());
            $mailDto->setBody($schedule->getInformation());

            $request->setMailDto($mailDto);
            $response = $this->notifierProxyLoggerBridge->insertMail($request);
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            throw $e;
        }

        return $response;
    }
}