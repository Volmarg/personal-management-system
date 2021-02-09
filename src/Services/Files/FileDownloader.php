<?php

namespace App\Services\Files;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloader extends AbstractController {

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory;

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(LoggerInterface $logger, Application $app) {
        $this->targetDirectory = Env::getUploadDir();
        $this->finder          = new Finder();
        $this->logger          = $logger;
        $this->app             = $app;
    }

    /**
     * @param $fileFullPath
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download($fileFullPath) {

        $message = $this->app->translator->translate('logs.download.startedDownloading');
        $this->logger->info($message, [
            'file_location' => $fileFullPath
        ]);

        try{
            if( !file_exists($fileFullPath) ){
                $message = $this->app->translator->translate('exceptions.download.theFileYouTryToDownloadDoesNotExist');
                throw new \Exception($message . $fileFullPath);
            }

            $logMessage = $this->app->translator->translate('logs.download.finishedDownloading');
            $file = $this->file($fileFullPath);
            $this->logger->info($logMessage);

            return $file;

        }catch(\Exception $e){

            $flashMessage = $this->app->translator->translate('flash.download.fileDoesNotExist');
            $logMessage   = $this->app->translator->translate('logs.download.exceptionWasThrownWhileDownloadingFile');

            $this->addFlash('danger', $flashMessage);
            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);

            return null;
        }

    }

}