<?php

namespace App\Command\Security;

use App\Controller\Core\Application;
use App\Services\Files\FilesHandler;
use App\Services\Security\EncryptionService;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DecryptUploadFilesCommand extends EncryptionCommand
{
    protected static $defaultName = 'pms:files:decrypt-upload-files';

    /**
     * @param EncryptionService $encryptionService
     * @param FilesHandler $filesHandler
     * @param Application $application
     * @param string|null $name
     */
    public function __construct(EncryptionService $encryptionService, FilesHandler $filesHandler, Application $application, string $name = null) {
        parent::__construct($encryptionService, $filesHandler, $application, $name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription("Handles encrypting upload modules based files - might take long depending on count of files to handle");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->io->success("Started");
        {
            try{

                $filesList   = $this->getFilesList();
                $progressBar = $this->io->createProgressBar();
                $progressBar->start(count($filesList));

                foreach($filesList as $fileAbsolutePath){
                    $this->io->write(PHP_EOL . "Now handling: {$fileAbsolutePath}");
                    $this->encryptionService->decryptFile($fileAbsolutePath);
                    $progressBar->advance();
                }

            }catch(\Exception $e){
                $this->application->logger->critical("There was an error.");
                $this->application->logger->critical($e->getMessage());
                $this->application->logger->critical($e->getTraceAsString());
                return self::FAILURE;
            }

        }
        $this->io->success("Finished");

        return self::SUCCESS;
    }

}
