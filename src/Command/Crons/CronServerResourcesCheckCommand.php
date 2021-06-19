<?php


namespace App\Command\Crons;

use App\Controller\Core\Application;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;

class CronServerResourcesCheckCommand extends Command
{
    protected static $defaultName = 'cron:check-server-resources';

    const LEFT_SERVER_DISC_SPACE_DANGER_VALUE_MBYTES = 122000;

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
            ->setDescription('This command allows to make backup of files for given upload modules and database, must be called as sudo to ensure directories creating. ');
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->note("Started checking server resources");
        {
            try{
                $this->checkServerDiscSpaceLeft();
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
    private function checkServerDiscSpaceLeft(): void
    {
        $discFreeSpaceInBytes  = disk_free_space("/");
        $discFreeSpaceInMBytes = round($discFreeSpaceInBytes / 1024 / 1024);

        if( $discFreeSpaceInMBytes <= self::LEFT_SERVER_DISC_SPACE_DANGER_VALUE_MBYTES ){
            $this->app->logger->emergency("Low disc space!", [
                "discSpaceLeft" => $discFreeSpaceInMBytes,
                "warningValue"  => self::LEFT_SERVER_DISC_SPACE_DANGER_VALUE_MBYTES,
            ]);
        }
    }

}
