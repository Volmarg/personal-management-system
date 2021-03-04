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

    const OPTION_TRANSFER_CHANNEL          = "transfer-channel";
    const OPTION_DUE_DATE_DAYS_TO_TRANSFER = "due-date-days-to-transfer";

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
     * @var array $dueDays
     */
    private array $dueDays;

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
            ->setDescription('Will transfer all schedules for date before current date to the NPL')
            ->addOption(self::OPTION_TRANSFER_CHANNEL, "type", InputOption::VALUE_REQUIRED, "What channel should be used to send the message later on" )
            ->addOption(self::OPTION_DUE_DATE_DAYS_TO_TRANSFER, "due-date", InputOption::VALUE_REQUIRED, "How many days before the deadline should the message be sent")
            ->addUsage("--transfer-channel=mail (Will send the schedules via mailing)")
            ->addUsage("--transfer-channel=discord (Will send the schedules via discord)")
            ->addUsage("--due-date-days-to-transfer=1,5,7 (Will send the schedules 1,5,7 days before the deadline)")
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
             $dueDays       = $input->getOption(self::OPTION_DUE_DATE_DAYS_TO_TRANSFER, null);

             if( empty($this->channel) ){
                 throw new Exception("No transfer channel was provided");
             }elseif( empty($dueDays) ){
                 throw new Exception("No due days were provided");
             }

             $this->dueDays = explode(",", $dueDays);
             if( empty($this->dueDays) ){
                 throw new Exception("Got due days parameter but could not build the array from provided value, maybe wrong separator?");
             }

             foreach($this->dueDays as $days){
                 if( !is_numeric($days) ){
                     throw new Exception("One of the days for the due days parameter is not numeric.");
                 }
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
                foreach($this->dueDays as $days){
                    // todo: test since it's adjusted now
                    $incomingSchedulesDTOS = $this->app->repositories->scheduleRepository->getIncomingSchedulesInformationInDays($days);

                    if( empty($incomingSchedulesDTOS) ){
                        $this->app->logger->info("No results to find for due days: {$days}");
                        continue;
                    }

                    foreach($incomingSchedulesDTOS as $incomingScheduleDTO){
                        $this->app->logger->info("Now handling schedule with id {$incomingScheduleDTO->getId()}");
                        $this->handleTransferForChannel($incomingScheduleDTO);
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
