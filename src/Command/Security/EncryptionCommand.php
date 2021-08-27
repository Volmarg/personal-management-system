<?php

namespace App\Command\Security;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Security\EncryptionService;
use DirectoryIterator;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class EncryptionCommand extends Command
{

    /**
     * @var SymfonyStyle $io
     */
    protected SymfonyStyle $io;

    /**
     * @var FilesHandler $filesHandler
     */
    protected FilesHandler $filesHandler;

    /**
     * @var Application $application
     */
    protected Application $application;

    /**
     * @var EncryptionService $encryptionService
     */
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService, FilesHandler $filesHandler, Application $application, string $name = null)
    {
        $this->application       = $application;
        $this->encryptionService = $encryptionService;
        $this->filesHandler      = $filesHandler;
        parent::__construct($name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $encryptionKey = $this->io->ask("Enter encryption key: ");
        $this->encryptionService->initialize($encryptionKey);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     * @return string[]
     */
    protected function getFilesList(): array
    {
        $filesPaths = [];
        $handledDirectories = [
            ...Env::getUploadDirs(),
            Env::getMiniaturesUploadDir(),
        ];
        foreach($handledDirectories as $uploadDirectory){

            $uploadDirectoryAbsolutePath = getcwd() . DIRECTORY_SEPARATOR . Env::getPublicRootDir() . DIRECTORY_SEPARATOR . $uploadDirectory;
            $absoluteFoldersList         = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator($uploadDirectoryAbsolutePath), false, true, true);

            $absoluteFoldersList[]  = $uploadDirectoryAbsolutePath;
            $filesListInDirectories = $this->filesHandler->listAllFilesInDirectories($absoluteFoldersList);

            foreach($filesListInDirectories as $directory => $files){
                foreach($files as $filename){

                    $fileAbsolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
                    $filesPaths[]     = $fileAbsolutePath;
                }
            }
        }

        return $filesPaths;
    }
}
