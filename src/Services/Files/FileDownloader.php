<?php

namespace App\Services\Files;

use App\Controller\Core\Env;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function __construct(
        LoggerInterface $logger,
        private readonly TranslatorInterface $translator
    ) {
        $this->targetDirectory = Env::getUploadDir();
        $this->finder          = new Finder();
        $this->logger          = $logger;
    }

    /**
     * @param $fileFullPath
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download($fileFullPath) {

        $message = $this->translator->trans('logs.download.startedDownloading');
        $this->logger->info($message, [
            'file_location' => $fileFullPath
        ]);

        try{
            if( !file_exists($fileFullPath) ){
                $message = $this->translator->trans('exceptions.download.theFileYouTryToDownloadDoesNotExist');
                throw new \Exception($message . $fileFullPath);
            }

            $logMessage = $this->translator->trans('logs.download.finishedDownloading');
            $file = $this->file($fileFullPath);
            $this->logger->info($logMessage);

            return $file;

        }catch(\Exception $e){

            $flashMessage = $this->translator->trans('flash.download.fileDoesNotExist');
            $logMessage   = $this->translator->trans('logs.download.exceptionWasThrownWhileDownloadingFile');

            $this->addFlash('danger', $flashMessage);
            $this->logger->info($logMessage, [
                'message' => $e->getMessage()
            ]);

            return null;
        }

    }

}