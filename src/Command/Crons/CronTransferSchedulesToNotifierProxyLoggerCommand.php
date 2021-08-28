<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\DTO\Modules\Schedules\IncomingScheduleDTO;
use App\Response\BaseResponse;
use App\Services\External\NotifierProxyLoggerService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronTransferSchedulesToNotifierProxyLoggerCommand extends Command
{
    protected static $defaultName = 'cron:transfer-schedules-to-notifier-proxy-logger';

    const OPTION_TRANSFER_CHANNEL = "transfer-channel";

    const TRANSFER_CHANNEL_MAIL    = "mail";
    const TRANSFER_CHANNEL_DISCORD = "discord";

    const ALL_TRANSFER_CHANNELS = [
        self::TRANSFER_CHANNEL_MAIL,
        self::TRANSFER_CHANNEL_DISCORD,
    ];

    /**
     * @var string $channel
     */
    private string $channel;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var NotifierProxyLoggerService $notifierProxyLoggerService
     */
    private NotifierProxyLoggerService $notifierProxyLoggerService;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, NotifierProxyLoggerService $notifierProxyLoggerService, Controllers $controllers)
    {
        parent::__construct(self::$defaultName);

        $this->app                        = $app;
        $this->controllers                = $controllers;
        $this->notifierProxyLoggerService = $notifierProxyLoggerService;
    }


    protected function configure()
    {
        $this
            ->setDescription('Will transfer all schedules with reminders to the NPL.')
            ->addOption(self::OPTION_TRANSFER_CHANNEL, "type", InputOption::VALUE_REQUIRED, "What channel should be used to send the message later on" )
            ->addUsage("--transfer-channel=mail (Will send the schedules via mailing)")
            ->addUsage("--transfer-channel=discord (Will send the schedules via discord)")
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
         $this->channel = $input->getOption(self::OPTION_TRANSFER_CHANNEL, null);

         if( empty($this->channel) ){
             throw new Exception("No transfer channel was provided");
         }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try{
            $this->app->logger->info("Started transferring the schedules to npl");
            {
                $incomingSchedulesDTOS = $this->app->repositories->myScheduleRepository->getSchedulesWithRemindersInformation();

                if( empty($incomingSchedulesDTOS) ){
                    $this->app->logger->info("No schedules were found to transfer");
                    return Command::SUCCESS;
                }

                foreach($incomingSchedulesDTOS as $incomingScheduleDTO){
                    $this->app->logger->info("Now handling schedule with id {$incomingScheduleDTO->getId()}, with reminder of id {$incomingScheduleDTO->getReminderId()}");
                    $response = $this->handleTransferForChannel($incomingScheduleDTO);

                    if($response->isSuccess()){
                        $reminder = $this->controllers->getMyScheduleReminderController()->findOneById($incomingScheduleDTO->getReminderId());
                        $reminder->setProcessed(true);
                        $this->controllers->getMyScheduleReminderController()->saveReminder($reminder);
                    }
                }
            }
            $this->app->logger->info("Finished transferring the schedules to npl");

        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Handle sending the schedule depending on the provided channel in the console (as option)
     *
     * @param IncomingScheduleDTO $incomingScheduleDTO
     * @return BaseResponse
     * @throws GuzzleException
     */
    private function handleTransferForChannel(IncomingScheduleDTO $incomingScheduleDTO): BaseResponse
    {
        switch($this->channel)
        {
            case self::TRANSFER_CHANNEL_DISCORD:
            {
                $response = $this->notifierProxyLoggerService->insertDiscordMessageForIncomingScheduleDto($incomingScheduleDTO);
            }
            break;

            case self::TRANSFER_CHANNEL_MAIL:
            {
                $response = $this->notifierProxyLoggerService->insertEmailForIncomingScheduleDto($incomingScheduleDTO);
            }
            break;

            default:
                throw new Exception("Unsupported channel {$this->channel}");
        }

        $this->app->logger->info("Got response", [
            $response->toJson(),
        ]);

        return $response;
    }
}
