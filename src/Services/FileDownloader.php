<?php

namespace App\Services;

use App\Controller\Utils\Env;
use Symfony\Component\Finder\Finder;

class FileDownloader {

    /**
     * @var string $targetDirectory
     */
    private $targetDirectory;

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct(Finder $finder) {
        $this->targetDirectory = Env::getUploadDir();
        $this->finder          = $finder;
    }

}