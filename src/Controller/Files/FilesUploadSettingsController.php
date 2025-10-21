<?php


namespace App\Controller\Files;

use App\Services\Files\DirectoriesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;

class FilesUploadSettingsController extends AbstractController {

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private $directoriesHandler;

    public function __construct(DirectoriesHandler $directoriesHandler) {
        $this->finder             = new Finder();
        $this->directoriesHandler = $directoriesHandler;
    }

}