<?php

namespace App\Command\Upload;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Services\Validation\FileValidatorService;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\ImageHandler;
use DirectoryIterator;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Avoid implementing:
 * - logic that might remove miniatures or normal files, as this might lead to removal of private data
 *
 * Class GenerateMiniaturesForImagesCommand
 * @package App\Command
 */
class GenerateMiniaturesForImagesCommand extends Command
{
    protected static $defaultName = 'pms:upload:generate-miniatures-for-images';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    /**
     * @var FilesHandler $filesHandler
     */
    private FilesHandler $filesHandler;

    /**
     * @var ImageHandler $imageHandler
     */
    private ImageHandler $imageHandler;

    /**
     * @var FileValidatorService $fileValidator
     */
    private FileValidatorService $fileValidator;

    public function __construct(Application $app, FilesHandler $filesHandler, ImageHandler $imageHandler, FileValidatorService $fileValidator, string $name = null) {
        parent::__construct($name);
        $this->filesHandler  = $filesHandler;
        $this->imageHandler  = $imageHandler;
        $this->app           = $app;
        $this->fileValidator = $fileValidator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription("
                This command will:
                 - generate miniatures for uploaded images if some are missing,
                 
                 Keep in mind:
                 - if file size is small enough, it's miniature will not be generated
            ");
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception|Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $processedUploadDirectories = [
            Env::getImagesUploadDir(),
        ];

        $this->io->success("Started");
        {
            try{
                foreach($processedUploadDirectories as $uploadDirectory){

                    $uploadDirectoryAbsolutePath = getcwd() . DIRECTORY_SEPARATOR . Env::getPublicRootDir() . DIRECTORY_SEPARATOR . $uploadDirectory;
                    $absoluteFoldersList         = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator($uploadDirectoryAbsolutePath), false, true);

                    $absoluteFoldersList[]  = $uploadDirectoryAbsolutePath;
                    $filesListInDirectories = $this->filesHandler->listAllFilesInDirectories($absoluteFoldersList);

                    foreach($filesListInDirectories as $directory => $files){
                        foreach($files as $filename){

                            $fileAbsolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
                            $fileObject       = new File($fileAbsolutePath);

                            preg_match('#\/upload\/(.*)#', $directory, $matches);

                            $uploadDirectoryStructure = $matches[1];

                            if( !array_key_exists(1, $matches) ){
                                $this->io->error("There is something wrong with this file, it's not in the upload directory? ");
                            }

                            $targetMiniatureFileAbsolutePath = getcwd() .
                                DIRECTORY_SEPARATOR .
                                Env::getPublicRootDir() .
                                DIRECTORY_SEPARATOR .
                                Env::getMiniaturesUploadDir() .
                                DIRECTORY_SEPARATOR .
                                $uploadDirectoryStructure .
                                DIRECTORY_SEPARATOR .
                                $filename;

                            if( file_exists($targetMiniatureFileAbsolutePath) ){
                                continue;
                            }

                            $this->io->note("Creating miniature for file");
                            $this->io->listing([
                               "from: " . $fileAbsolutePath,
                               "to : "  . $targetMiniatureFileAbsolutePath,
                            ]);

                            if( !$this->fileValidator->isFileImage($fileObject) ){
                                $this->io->warning("File is not an image");
                                continue;
                            }

                            if( !$this->fileValidator->isImageResizable($fileObject) ){
                                $this->io->warning("Image type is not resizable");
                                continue;
                            }

                            $this->imageHandler->createMiniature($fileAbsolutePath, true, $targetMiniatureFileAbsolutePath);
                            $this->io->listing([
                                "status: " . $this->imageHandler->getLastStatus(),
                            ]);
                        }
                    }
                }

            }catch(\Exception $e){
                $this->app->logger->critical("There was an error.");
                $this->app->logger->critical($e->getMessage());
                $this->app->logger->critical($e->getTraceAsString());
                return 1;
            }

        }
        $this->io->success("Finished");

        return 0;
    }

}
