<?php

namespace App\Command\Crons;

use App\Controller\Core\Application;
use App\Entity\Modules\Schedules\MySchedule;
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
     * @var array $due_days
     */
    private array $due_days;

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var NotifierProxyLoggerService $notifier_proxy_logger_service
     */
    private NotifierProxyLoggerService $notifier_proxy_logger_service;

    public function __construct(Application $app, NotifierProxyLoggerService $notifier_proxy_logger_service)
    {
        parent::__construct(self::$defaultName);

        $this->app                           = $app;
        $this->notifier_proxy_logger_service = $notifier_proxy_logger_service;
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
             $due_days      = $input->getOption(self::OPTION_DUE_DATE_DAYS_TO_TRANSFER, null);

             if( empty($this->channel) ){
                 throw new Exception("No transfer channel was provided");
             }elseif( empty($due_days) ){
                 throw new Exception("No due days were provided");
             }

             $this->due_days = explode(",", $due_days);
             if( empty($this->due_days) ){
                 throw new Exception("Got due days parameter but could not build the array from provided value, maybe wrong separator?");
             }

             foreach($this->due_days as $days){
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
                foreach($this->due_days as $days){
                    $schedules_to_handle = $this->app->repositories->myScheduleRepository->getIncomingSchedulesEntitiesInDays($days);

                    if( empty($schedules_to_handle) ){
                        $this->app->logger->info("No results to find for due days: {$days}");
                        continue;
                    }

                    foreach($schedules_to_handle as $schedule){
                        $this->app->logger->info("Now handling schedule with id {$schedule->getId()}");
                        $this->handleTransferForChannel($schedule);
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
     * @param MySchedule $schedule
     * @throws Exception|GuzzleException
     */
    private function handleTransferForChannel(MySchedule $schedule): void
    {
        switch($this->channel)
        {
            case self::TRANSFER_CHANNEL_DISCORD:
            {
                $response = $this->notifier_proxy_logger_service->insertDiscordMessageForSchedule($schedule);
            }
            break;

            case self::TRANSFER_CHANNEL_MAIL:
            {
                $response = $this->notifier_proxy_logger_service->insertEmailForSchedule($schedule);
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
