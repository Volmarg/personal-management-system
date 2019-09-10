<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Utils\Application;
use App\Services\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class FilesTagsController extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FileTagger $fileTagger
     */
    private $fileTagger;

    public function __construct(Application $app, FileTagger $file_tagger) {
        $this->app        = $app;
        $this->fileTagger = $file_tagger;
    }

    /**
     * @Route("api/files-tagger/update-tags", name="api_files_tagger_update_tags", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function apiUpdateTags(Request $request): Response {

        if (!$request->request->has(FileTagger::KEY_TAGS)){
            throw new \Exception("Request is missing key: ".FileTagger::KEY_TAGS);
        }

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            throw new \Exception("Request is missing key: ".MyFilesController::KEY_FILE_FULL_PATH);
        }

        if( empty($file_full_path) ){
            return new Response("File path is an empty string");
        }

        $tags_string    = $request->request->get(FileTagger::KEY_TAGS);
        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);

        $response = $this->updateTags($tags_string, $file_full_path);
        return $response;
    }

    public function updateTags(string $tags_string, string $file_full_path): Response {

        $array_of_tags  = explode(',', $tags_string);

        try{
            $this->fileTagger->prepare($array_of_tags, $file_full_path);
            $response = $this->fileTagger->updateTags();
        }catch(\Exception $e){
            $response = new Response("There was an error while trying to update tags via api call");
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function apiRemoveTags(Request $request): Response {

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            throw new \Exception("Request is missing key: ".MyFilesController::KEY_FILE_FULL_PATH);
        }

        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);
        $response       = $this->removeTags($file_full_path);

        return $response;
    }

    /**
     * @param string $file_full_path
     * @return Response
     * @throws \Exception
     */
    public function removeTags(string $file_full_path): Response {
        $this->fileTagger->prepare([], $file_full_path);
        $response = $this->fileTagger->removeTags();
        return $response;
    }

}