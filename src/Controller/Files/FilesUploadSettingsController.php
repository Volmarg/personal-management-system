<?php


namespace App\Controller\Files;

use App\Services\Files\DirectoriesHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param string $uploadType
     * @param string $currentDirectoryPathInModuleUploadDir
     * @param string $newName
     * @return Response
     * @throws Exception
     */
    public function renameSubdirectory(?string $uploadType, ?string $currentDirectoryPathInModuleUploadDir, ?string $newName){
        $response = $this->directoriesHandler->renameSubdirectory($uploadType, $currentDirectoryPathInModuleUploadDir, $newName);
        return $response;
    }

}