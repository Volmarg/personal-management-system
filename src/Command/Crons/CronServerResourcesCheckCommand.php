<?php


namespace App\Command\Crons;

use App\Controller\Core\Application;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;

class CronServerResourcesCheckCommand extends Command
{
    protected static $defaultName = 'cron:check-server-resources';

    const ARGUMENT_LOW_DISC_SPACE_DANGER_MBYTES = "dangerLowSpace";
    const ARGUMENT_CHECKED_PATH                 = "checkedPath";

    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    /**
     * @var Application $app
     */
    private Application $app;

    public function __construct(Application $app, string $name = null) {
        parent::__construct($name);
        $this->app = $app;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command checks the free resources left on the server')
            ->addArgument(self::ARGUMENT_CHECKED_PATH, InputArgument::REQUIRED, "Path that should be checked for space left")
            ->addArgument(self::ARGUMENT_LOW_DISC_SPACE_DANGER_MBYTES, InputArgument::REQUIRED, "Number of MBytes left for which mail is being sent")
            ->addUsage(" '/' 2000");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->note("Started checking server resources");
        {
            try{
                $dangerLowSpace = $input->getArgument(self::ARGUMENT_LOW_DISC_SPACE_DANGER_MBYTES);
                $checkedPath    = $input->getArgument(self::ARGUMENT_CHECKED_PATH);
                $this->checkServerDiscSpaceLeft($dangerLowSpace, $checkedPath);
            }catch(Exception | TypeError $e){
                $this->app->logger->emergency("Exception was thrown while trying to check the server resources.", [
                    "exceptionMessage" => $e->getMessage(),
                ]);

                return self::FAILURE;
            }
        }
        $this->io->note("Finished checking server resources");

        return self::SUCCESS;
    }

    /**
     * Will check how much left disc space is there,
     * Triggers emergency logger if value is below given threshold
     */
    private function checkServerDiscSpaceLeft(int $dangerLowSpace, string $checkedPath): void
    {
        $discFreeSpaceInBytes  = disk_free_space($checkedPath);
        $discFreeSpaceInMBytes = round($discFreeSpaceInBytes / 1024 / 1024);

        if( $discFreeSpaceInMBytes <= $dangerLowSpace ){
            $this->app->logger->emergency("Low disc space!", [
                "discSpaceLeft" => $discFreeSpaceInMBytes,
                "warningValue"  => $dangerLowSpace,
            ]);
        }
    }

}
