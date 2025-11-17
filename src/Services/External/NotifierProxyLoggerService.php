<?php

namespace App\Services\External;

use App\DTO\Discord\DiscordMessageDTO;
use App\DTO\Mail\MailDTO;
use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\NotifierProxyLoggerBridge;
use App\Request\Discord\InsertDiscordMessageRequest;
use App\Request\Mail\InsertMailRequest;
use App\Response\Discord\InsertDiscordMessageResponse;
use App\Response\Mail\InsertMailResponse;
use App\Services\ConfigLoaders\ConfigLoaderSystem;
use App\Services\System\EnvReader;
use App\Traits\ExceptionLoggerAwareTrait;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Handles communication with NPL
 *
 * Class NotifierProxyLoggerService
 * @package App\Services\External
 */
class NotifierProxyLoggerService
{
    use ExceptionLoggerAwareTrait;

    const MESSAGE_TITLE_PREFIX_SCHEDULE = "[PMS Calendar schedule] ";

    /**
     * @var NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     */
    private NotifierProxyLoggerBridge $notifierProxyLoggerBridge;

    /**
     * NotifierProxyLoggerService constructor.
     *
     * @param NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     * @param ConfigLoaderSystem        $configLoaderSystem
     * @param LoggerInterface           $logger
     */
    public function __construct(
        NotifierProxyLoggerBridge $notifierProxyLoggerBridge,
        private readonly ConfigLoaderSystem $configLoaderSystem,
        private readonly LoggerInterface $logger,
    )
    {
        $this->notifierProxyLoggerBridge = $notifierProxyLoggerBridge;
    }

    /**
     * Will use IncomingScheduleDTO and insert single discord message to the queue in NPL
     *
     * @param IncomingScheduleDTO $incomingScheduleDTO
     * @return InsertDiscordMessageResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function insertDiscordMessageForIncomingScheduleDto(IncomingScheduleDTO $incomingScheduleDTO): InsertDiscordMessageResponse
    {
        try{
            $discordMessageDto = new DiscordMessageDTO();
            $request           = new InsertDiscordMessageRequest();

            $discordMessageDto->setWebhookName(NotifierProxyLoggerBridge::WEBHOOK_NAME_ALL_NOTIFICATIONS);
            $discordMessageDto->setMessageTitle(self::MESSAGE_TITLE_PREFIX_SCHEDULE . $incomingScheduleDTO->getTitle());
            $discordMessageDto->setMessageContent($incomingScheduleDTO->getBody());
            $discordMessageDto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);

            $request->setDiscordMessageDto($discordMessageDto);
            $response = $this->notifierProxyLoggerBridge->insertDiscordMessage($request);
        }catch(Exception $e){
            $this->logException($e);
            throw $e;
        }

        return $response;
    }

    /**
     * Will use IncomingScheduleDTO and insert single email to the queue in NPL
     *
     * @param IncomingScheduleDTO $incomingScheduleDTO
     * @return InsertMailResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function insertEmailForIncomingScheduleDto(IncomingScheduleDTO $incomingScheduleDTO): InsertMailResponse
    {
        try{
            $mailDto = new MailDTO();
            $request = new InsertMailRequest();

            $mailDto->setToEmails(EnvReader::getNotifierProxyLoggerDefaultReceiversEmails());
            $mailDto->setSource(NotifierProxyLoggerBridge::SOURCE_PMS);
            $mailDto->setFromEmail($this->configLoaderSystem->getSystemFromEmail());
            $mailDto->setSubject(self::MESSAGE_TITLE_PREFIX_SCHEDULE . $incomingScheduleDTO->getTitle());
            $mailDto->setBody($incomingScheduleDTO->getBody());

            $request->setMailDto($mailDto);
            $response = $this->notifierProxyLoggerBridge->insertMail($request);
        }catch(Exception $e){
            $this->logException($e);
            throw $e;
        }

        return $response;
    }
}