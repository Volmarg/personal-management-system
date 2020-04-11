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
     * @var string $target_directory
     */
    private $target_directory;

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
        $this->target_directory = Env::getUploadDir();
        $this->finder           = new Finder();
        $this->logger           = $logger;
        $this->app              = $app;
    }

    /**
     * @param $file_full_path
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download($file_full_path) {

        $message = $this->app->translator->translate('logs.download.startedDownloading');
        $this->logger->info($message, [
            'file_location' => $file_full_path
        ]);

        try{
            if( !file_exists($file_full_path) ){
                $message = $this->app->translator->translate('exceptions.download.theFileYouTryToDownloadDoesNotExist');
                throw new \Exception($message . $file_full_path);
            }

            $log_message = $this->app->translator->translate('logs.download.finishedDownloading');
            $file = $this->file($file_full_path);
            $this->logger->info($log_message);

            return $file;

        }catch(\Exception $e){

            $flash_message  = $this->app->translator->translate('flash.download.fileDoesNotExist');
            $log_message    = $this->app->translator->translate('logs.download.exceptionWasThrownWhileDownloadingFile');

            $this->addFlash('danger', $flash_message);
            $this->logger->info($log_message, [
                'message' => $e->getMessage()
            ]);

            return null;
        }

    }

}