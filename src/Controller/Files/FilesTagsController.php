<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Core\Application;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param string $tags_string
     * @param string $file_full_path
     * @return Response
     * 
     */
    public function updateTags(string $tags_string, string $file_full_path): Response {

        $array_of_tags  = explode(',', $tags_string);

        try{
            $this->file_tagger->prepare($array_of_tags, $file_full_path);
            $response = $this->file_tagger->updateTags();
        }catch(Exception $e){
            $message  = $this->app->translator->translate('responses.tags.errorWhileTryingToUpdateTagsViaApi');
            $response = new Response($message);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function apiRemoveTags(Request $request): Response {

        if (!$request->request->has(MyFilesController::KEY_FILE_FULL_PATH)){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . MyFilesController::KEY_FILE_FULL_PATH;
            throw new Exception($message);
        }

        $file_full_path = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);
        $response       = $this->removeTags($file_full_path);

        return $response;
    }

    /**
     * @param string $file_full_path
     * @return Response
     * @throws Exception
     */
    public function removeTags(string $file_full_path): Response {
        $this->file_tagger->prepare([], $file_full_path);
        $response = $this->file_tagger->removeTags();
        return $response;
    }

}