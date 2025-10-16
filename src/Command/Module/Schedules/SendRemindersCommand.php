<?php

namespace App\Command\Module\Schedules;

use App\DTO\NotificationDto;
use App\DTO\Settings\Notifications\ConfigDto;
use App\DTO\Settings\SettingNotificationDto;
use App\Entity\Modules\Schedules\MyScheduleReminder;
use App\Repository\Modules\Schedules\MyScheduleRepository;
use App\Services\External\DiscordService;
use App\Services\Settings\SettingsLoader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand('schedules:send-reminders', 'Send reminders over configured notification handlers')]
class SendRemindersCommand extends Command
{
    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    /**
     * @var string $reminderUserName
     */
    private string $reminderUserName;

    /**
     * @param MyScheduleRepository   $myScheduleRepository
     * @param SettingsLoader         $settingsLoader
     * @param LoggerInterface        $logger
     * @param ParameterBagInterface  $parameterBag
     * @param DiscordService         $discordService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly MyScheduleRepository   $myScheduleRepository,
        private readonly SettingsLoader         $settingsLoader,
        private readonly LoggerInterface        $logger,
        readonly ParameterBagInterface          $parameterBag,
        private readonly DiscordService         $discordService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(self::$defaultName);
        $this->reminderUserName = $parameterBag->get('project.name') . " Reminder";

    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $io                 = new SymfonyStyle($input, $output);
            $setting            = $this->settingsLoader->getSettingsForNotifications();
            $notificationConfig = SettingNotificationDto::fromJson($setting->getValue());

            if (empty($notificationConfig->getConfig())) {
                $io->info("No notification configs were found");
                return Command::INVALID;
            }

            $incomingSchedulesDTOS = $this->myScheduleRepository->getSchedulesWithRemindersInformation();
            foreach ($incomingSchedulesDTOS as $scheduleDto) {
                $title = "Schedule: {$scheduleDto->getTitle()} - {$scheduleDto->getDate()}";
                $notificationDto = new NotificationDto(title: $title, message: $scheduleDto->getBody(), username: $this->reminderUserName);
                $this->handleNotifications($notificationDto, $notificationConfig->getConfig());

                $reminder = $this->entityManager->find(MyScheduleReminder::class, $scheduleDto->getReminderId());
                $reminder->setProcessed(true);

                $this->entityManager->persist($reminder);
                $this->entityManager->flush();
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param NotificationDto $notificationDto
     * @param array           $notificationConfigs
     *
     * @throws GuzzleException
     */
    private function handleNotifications(NotificationDto $notificationDto, array $notificationConfigs): void
    {
        foreach ($notificationConfigs as $config) {
            if (!$config->isActiveForReminder()) {
                continue;
            }

            if (empty($config->getValue())) {
                $this->io->info("Config value for this config is empty: {$config->getName()}");
                continue;
            }

            switch ($config->getName()) {
                case ConfigDto::NAME_DISCORD_WEBHOOK:
                    $notificationDto->setUrl($config->getValue());
                    $this->discordService->sendWebhookMessage($notificationDto);
                break;
            };
        }
    }

}
