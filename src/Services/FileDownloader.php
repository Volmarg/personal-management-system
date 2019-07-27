<?php

namespace App\Services;

use App\Controller\Utils\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function __construct() {
        $this->targetDirectory = Env::getUploadDir();
        $this->finder          = new Finder();
    }

    /**
     * @param $file_full_path
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download($file_full_path)
    {
        if( !file_exists($file_full_path) ){
            throw new \Exception("The file that You are trying to download, does not exist. {$file_full_path}");
        }

        $file = $this->file($file_full_path);
        return $file;
    }

}