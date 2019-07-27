<?php

namespace App\Services;

use App\Controller\Utils\Env;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

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
     * @var Logger $logger
     */
    private $logger;

    public function __construct(Logger $logger) {
        $this->targetDirectory  = Env::getUploadDir();
        $this->finder           = new Finder();
        $this->logger           = $logger;

    }

    /**
     * @param $file_full_path
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download($file_full_path)
    {
        $this->logger->info('Started downloading file: ', [
            'file_location' => $file_full_path
        ]);


        try{
            if( !file_exists($file_full_path) ){
                throw new \Exception("The file that You are trying to download, does not exist. {$file_full_path}");
            }
        }catch(\Exception $e){
            $this->addFlash('danger', 'Requested file does not exist!');
            $this->logger->info('Exception was thrown while downloading file: ', [
                'message' => $e->getMessage()
            ]);

        }

        $file = $this->file($file_full_path);
        $this->logger->info('Finished downloading data.');

        return $file;
    }

}