<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Core\Application;
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
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    public function __construct(Application $app, FileTagger $file_tagger) {
        $this->app         = $app;
        $this->file_tagger = $file_tagger;
    }

    /**
     * @Route("api/files-tagger/update-tags", name="api_files_tagger_update_tags", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function apiUpdateTags(Request $request): Response {

        if (!$request->request->has(FileTagger::KEY_TAGS)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileTagger::KEY_TAGS;
            throw new \Exception($message);
        }

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new \Exception($message);
        }

        if( empty($file_full_path) ){
            $message = $this->app->translator->translate('responses.files.filePathIsAnEmptyString');
            return new Response($message);
        }

        $tags_string    = $request->request->get(FileTagger::KEY_TAGS);
        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);

        $response = $this->updateTags($tags_string, $file_full_path);
        return $response;
    }

    public function updateTags(string $tags_string, string $file_full_path): Response {

        $array_of_tags  = explode(',', $tags_string);

        try{
            $this->file_tagger->prepare($array_of_tags, $file_full_path);
            $response = $this->file_tagger->updateTags();
        }catch(\Exception $e){
            $message  = $this->app->translator->translate('responses.tags.errorWhileTryingToUpdateTagsViaApi');
            $response = new Response($message);
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
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new \Exception($message);
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
        $this->file_tagger->prepare([], $file_full_path);
        $response = $this->file_tagger->removeTags();
        return $response;
    }

}