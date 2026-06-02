<?php

namespace App\Command\Module\Storage;

use App\Enum\StorageModuleEnum;
use App\Services\Module\Storage\StorageFileService;
use App\Services\Module\Storage\StorageService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    'storage:upload-files-into-entities',
    'Takes the uploaded files and creates DB entries for each (if it does not exist yet)'
)]
class UploadFilesIntoEntitiesCommand extends Command
{
    private readonly SymfonyStyle $io;

    public function __construct(
        private readonly LoggerInterface        $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly StorageService         $storageService,
        private readonly StorageFileService  $storageFileService,
    ) {
        parent::__construct(self::$defaultName);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $filesData = [];
            foreach (StorageModuleEnum::cases() as $enum) {
                $this->storageService->getTreeData($enum, $filesData);
            }

            $addedFilesRows = [];
            foreach ($filesData as $fileData) {
                $extPart = empty($fileData['ext']) ? "" : "." . $fileData['ext'];
                $filePath = $fileData['dir'] . DIRECTORY_SEPARATOR . $fileData['name'] . $extPart;

                $filePath = $this->storageFileService->uploadedFileIntoEntity($filePath, false);
                if (!is_null($filePath)) {
                    $addedFilesRows[] = [$filePath];
                }
            }

            if (empty($addedFilesRows)) {
                $this->io->info("No files to add");
                return self::SUCCESS;
            }

            $this->io->info("Adding files");
            $this->io->table(["File path"], $addedFilesRows);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
