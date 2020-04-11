<?php


namespace App\Controller\Files;

use App\Controller\Core\Application;
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
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(DirectoriesHandler $directories_handler, Application $app) {
        $this->app                    = $app;
        $this->finder                 = new Finder();
        $this->directories_handler    = $directories_handler;
    }

    /**
     * @param string $upload_type
     * @param string $current_directory_path_in_module_upload_dir
     * @param string $new_name
     * @return Response
     * @throws Exception
     */
    public function renameSubdirectory(?string $upload_type, ?string $current_directory_path_in_module_upload_dir, ?string $new_name){
        $response = $this->directories_handler->renameSubdirectory($upload_type, $current_directory_path_in_module_upload_dir, $new_name);
        return $response;
    }

}