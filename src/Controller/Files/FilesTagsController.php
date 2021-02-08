<?php


namespace App\Controller\Files;

use App\Controller\Modules\Files\MyFilesController;
use App\Controller\Core\Application;
use App\Entity\FilesTags;
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
     * @var FileTagger $fileTagger
     */
    private $fileTagger;

    public function __construct(Application $app, FileTagger $fileTagger) {
        $this->app        = $app;
        $this->fileTagger = $fileTagger;
    }

    /**
     * @param string $tagsString
     * @param string $fileFullPath
     * @return Response
     * 
     */
    public function updateTags(string $tagsString, string $fileFullPath): Response {

        $arrayOfTags  = explode(',', $tagsString);

        try{
            $this->fileTagger->prepare($arrayOfTags, $fileFullPath);
            $response = $this->fileTagger->updateTags();
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

        $fileFullPath = $request->request->get(MyFilesController::KEY_FILE_FULL_PATH);
        $response     = $this->removeTags($fileFullPath);

        return $response;
    }

    /**
     * @param string $fileFullPath
     * @return Response
     * @throws Exception
     */
    public function removeTags(string $fileFullPath): Response {
        $this->fileTagger->prepare([], $fileFullPath);
        $response = $this->fileTagger->removeTags();
        return $response;
    }

    /**
     * Will return tags entity for given file path if exists, or null if does not
     *
     * @param string $fileFullPath
     * @return FilesTags|null
     */
    public function getFileTagsEntityByFileFullPath(string $fileFullPath): ?FilesTags
    {
        return $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($fileFullPath);
    }
}