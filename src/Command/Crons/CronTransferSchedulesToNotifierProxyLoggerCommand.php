<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\DTO\Modules\Schedules\IncomingScheduleDTO;
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
     * This is a temporary solution to create a window for example for cron to read the schedule, instead of having issue
     * where the cron is running like each 5 min and will never hit the exact date for reminder.
     */
    const SCHEDULE_SAFETY_TIME_OFFSET = 10; //in minutes

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

    public function __construct(Application $app, NotifierProxyLoggerService $notifierProxyLoggerService)
    {
        parent::__construct(self::$defaultName);

        $this->app                        = $app;
        $this->notifierProxyLoggerService = $notifierProxyLoggerService;
    }


    protected function configure()
    {
        $this
            ->setDescription('Will transfer all schedules with reminders to the NPL. With current configuration, cron should be called each 6-7min')
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
                $incomingSchedulesDTOS = $this->app->repositories->myScheduleRepository->getSchedulesWithRemindersDueDatesInformation(self::SCHEDULE_SAFETY_TIME_OFFSET);

                if( empty($incomingSchedulesDTOS) ){
                    $this->app->logger->info("No schedules were found to transfer");
                    return Command::SUCCESS;
                }

                foreach($incomingSchedulesDTOS as $incomingScheduleDTO){
                    $this->app->logger->info("Now handling schedule with id {$incomingScheduleDTO->getId()}");
                    $this->handleTransferForChannel($incomingScheduleDTO);
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
     * @throws GuzzleException
     */
    private function handleTransferForChannel(IncomingScheduleDTO $incomingScheduleDTO): void
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
    }
}
