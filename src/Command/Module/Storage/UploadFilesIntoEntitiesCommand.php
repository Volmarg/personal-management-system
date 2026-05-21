<?php

namespace App\Command\Module\Storage;

use App\Entity\Modules\Storage\StorageFile;
use App\Enum\StorageModuleEnum;
use App\Repository\Modules\Storage\StorageFileRepository;
use App\Services\Files\PathService;
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
        private readonly StorageFileRepository  $storageFileRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly StorageService         $storageService
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
                [$storageEntries, $filesData] = $this->storageService->getTreeData($enum, $filesData);
            }

            $addedFilesRows = [];
            foreach ($filesData as $fileData) {
                $filePath = $fileData['dir'] . $fileData['name'] . "." . $fileData['ext'];

                $publicPath = $filePath;
                if (!str_starts_with($publicPath, 'public')) {
                    $publicPath = "public/{$filePath}";
                }

                $storageModule = PathService::getStorageModuleByPath($publicPath);
                if (!$this->storageFileRepository->exists($filePath)) {
                    $storageFile = new StorageFile($filePath, $storageModule->value);
                    $addedFilesRows[] = [$filePath];
                    $this->entityManager->persist($storageFile);
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
