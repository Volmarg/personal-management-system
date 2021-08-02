<?php

namespace App\Action\Files;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Form\Files\UploadSubdirectoryCreateType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use App\Services\Routing\UrlMatcherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TypeError;

/**
 * @ModuleAnnotation(
 *     relatedModules=App\Controller\Modules\ModulesController::UPLOAD_MENU_RELATED_MODULES
 * )
 */
class FilesAction extends AbstractController {

    const KEY_RESPONSE_CODE         = 'response_code';
    const KEY_RESPONSE_MESSAGE      = 'response_message';
    const KEY_RESPONSE_DATA         = 'response_data';
    const KEY_RESPONSE_ERRORS_DATA  = 'response_errors_data';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var FilesHandler $filesHandler
     */
    private FilesHandler $filesHandler;

    /**
     * @var FileTagger $fileTagger
     */
    private FileTagger $fileTagger;

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private DirectoriesHandler $directoriesHandler;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var UrlMatcherService $urlMatcherService
     */
    private UrlMatcherService $urlMatcherService;

    public function __construct(
        Application        $app,
        FilesHandler       $filesHandler,
        FileTagger         $fileTagger,
        DirectoriesHandler $directoriesHandler,
        Controllers        $controllers,
        UrlMatcherService  $urlMatcherService
    ) {
        $this->app                = $app;
        $this->filesHandler       = $filesHandler;
        $this->fileTagger         = $fileTagger;
        $this->directoriesHandler = $directoriesHandler;
        $this->controllers        = $controllers;
        $this->urlMatcherService  = $urlMatcherService;
    }

    /**
     * @Route("/files/action/remove-file", name="files_remove_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function removeFileViaPost(Request $request) {
        $response = $this->filesHandler->removeFile($request);

        $code    = $response->getStatusCode();
        $message = $response->getContent();

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }

    /**
     * @Route("/files/action/rename-file", name="files_rename_file", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function renameFileViaPost(Request $request) {

        $updateFilePath = function ($currRelativeFilepath, $newRelativeFilePath) {
            $this->fileTagger->updateFilePath($currRelativeFilepath, $newRelativeFilePath);
            $this->controllers->getLockedResourceController()->updatePath($currRelativeFilepath, $newRelativeFilePath);
        };

        $response = $this->filesHandler->renameFileViaRequest($request, $updateFilePath);
        return $response;
    }

    /**
     * @Route("/files/action/move-multiple-files", name="move_multiple_multiple", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function moveMultipleFilesViaPost(Request $request){

        if( !$request->request->has(FilesHandler::KEY_FILES_CURRENT_PATHS) ){
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_FILES_CURRENT_PATHS;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if (!$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            $this->app->logger->warning($message);;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_BAD_REQUEST, $message);
        }

        /** @info: this will be used to build single file transfer for each path */
        $filesCurrentPaths = $request->request->get(FilesHandler::KEY_FILES_CURRENT_PATHS);

        $responseErrorsData  = [];
        $responseSuccessData = [];

        foreach($filesCurrentPaths as $fileCurrentPath){
            $targetModuleUploadDir                   = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);
            $subdirectoryTargetPathInModuleUploadDir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

            $request = new Request();
            $request->request->set(FilesHandler::KEY_FILE_CURRENT_PATH, $fileCurrentPath);
            $request->request->set(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR, $targetModuleUploadDir);
            $request->request->set(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, $subdirectoryTargetPathInModuleUploadDir);

            try{
                $response = $this->moveSingleFileViaPost($request);
            }catch(Exception $e){
                $message  = $this->app->translator->translate("responses.files.thereWasAnErrorWhileTryingToMoveFile");
                $response = new Response($message);

                $this->app->logger->warning($message);;
                return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
            }
            $responseData = json_decode($response->getContent(), true);

            if( array_key_exists(self::KEY_RESPONSE_CODE, $responseData) ){

                if( $response->getStatusCode() >= 300){
                    $responseErrorsData[] = [
                        self:: KEY_RESPONSE_DATA            => $responseData,
                        FilesHandler::KEY_FILE_CURRENT_PATH => $fileCurrentPath,
                    ];
                }else{
                    $responseSuccessData = $responseData;
                }

            }

        }

        //all files copied
        if( empty($responseErrorsData) ){
            $message = $this->app->translator->translate('responses.files.filesHasBeenSuccesfullyMoved');
            $code    = Response::HTTP_OK;
        }else{

            // all failed
            $message = $this->app->translator->translate('responses.files.couldNotTheFiles');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;

            // some failed
            if( !empty($responseSuccessData) && !empty($responseErrorsData) ) {
                $message = $this->app->translator->translate('responses.files.couldNotMoveSomeFiles');
                $code    = Response::HTTP_ACCEPTED;
            }

            $this->app->logger->warning($message, [
                self::KEY_RESPONSE_ERRORS_DATA => $responseErrorsData
            ]);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall($code, $message);
    }


    /**
     * @Route("/files/action/move-single-file", name="move_single_file", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function moveSingleFileViaPost(Request $request) {

        if (!$request->request->has(FilesHandler::KEY_FILE_CURRENT_PATH)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_FILE_CURRENT_PATH;
            throw new \Exception($message);
        }

        if (!$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            throw new \Exception($message);
        }

        if (!$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR)) {
            $message = $this->app->translator->translate('exceptions.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            throw new \Exception($message);
        }

        $subdirectoryTargetPathInModuleUploadDir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);
        $currentFileLocation                      = $request->request->get(FilesHandler::KEY_FILE_CURRENT_PATH);
        $targetModuleUploadDir                    = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);

        $targetDirectoryInUploadDir = FileUploadController::getTargetDirectoryForUploadModuleDir($targetModuleUploadDir);

        #checks if selected folder is the main upload dir (Main Folder)
        if ( $subdirectoryTargetPathInModuleUploadDir === $targetDirectoryInUploadDir ){
            $subdirectoryPathInUploadDir = $targetDirectoryInUploadDir;
        }else{
            $subdirectoryPathInUploadDir  = $targetDirectoryInUploadDir.DIRECTORY_SEPARATOR.$subdirectoryTargetPathInModuleUploadDir;
        }

        $filename           = basename($currentFileLocation);
        $targetFileLocation = FilesHandler::buildFileFullPathFromDirLocationAndFileName($subdirectoryPathInUploadDir, $filename);

        $response = $this->filesHandler->moveSingleFile($currentFileLocation, $targetFileLocation);

        $responseData = [
            self::KEY_RESPONSE_MESSAGE => $response->getContent(),
            self::KEY_RESPONSE_CODE    => $response->getStatusCode(),
        ];

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/files/{uploadModuleDir}/remove-subdirectory", name="upload_remove_subdirectory", methods="POST")
     * @param string $uploadModuleDir
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function removeFolderByPostRequest(string $uploadModuleDir, Request $request) {

        $blockRemoval = false;

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message  = $this->app->translator->translate('exceptions.files.subdirectoryLocationMissingInRequest');
            $response = new Response($message, 500);
        }else{

            $currentDirectoryPathInModuleUploadDir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
            if( in_array( $currentDirectoryPathInModuleUploadDir, $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule() ) ) {
                $message = $this->app->translator->translate('exceptions.files.cannotRemoveMainFolder');
                $response = new Response($message, 500);
            }
            else {

                if ( $request->request->has(DirectoriesHandler::KEY_BLOCK_REMOVAL) ) {
                    $blockRemoval = true;
                }

                $response = $this->directoriesHandler->removeFolder($uploadModuleDir, $currentDirectoryPathInModuleUploadDir, $blockRemoval);

            }

        }

        $responseData = [
            'message' => $response->getContent(),
            'code'    => $response->getStatusCode()
        ];

        return new JsonResponse($responseData);
    }

    /**
     * It's possible to either call this method by using keys directly in data passed in ajax or serialized form
     * @Route("/files/actions/create-folder", name="action_create_subdirectory", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws \Exception
     */
    public function createSubdirectoryByPostRequest(Request $request){

        $isForm = $request->request->has(UploadSubdirectoryCreateType::FORM_NAME);

        switch ($isForm) {

            case true:

                $form = $request->request->get(UploadSubdirectoryCreateType::FORM_NAME);

                if ( !array_key_exists(FileUploadController::KEY_SUBDIRECTORY_NAME, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !array_key_exists(FileUploadController::KEY_UPLOAD_MODULE_DIR, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_UPLOAD_MODULE_DIR;
                    return new Response($message, 500);
                }

                if ( !array_key_exists(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR, $form) ) {
                    $message = $this->app->translator->translate('responses.general.missingFormInput') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
                    return new Response($message, 500);
                }

                $subdirectoryName = $form[FileUploadController::KEY_SUBDIRECTORY_NAME];
                $uploadModuleDir  = $form[FileUploadController::KEY_UPLOAD_MODULE_DIR];
                $targetDirectoryPathInModuleUploadDir = $form[FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR];

                break;
            case false:

                if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_NAME) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !$request->request->has(FileUploadController::KEY_UPLOAD_MODULE_DIR) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR) ) {
                    $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NAME;
                    return new Response($message, 500);
                }

                $subdirectoryName  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_NAME);
                $uploadModuleDir  = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
                $targetDirectoryPathInModuleUploadDir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

                break;
        }

        $response = $this->directoriesHandler->createFolder($uploadModuleDir, $subdirectoryName, $targetDirectoryPathInModuleUploadDir);

        $responseData = [
            'message' => $response->getContent(),
            'code'    => $response->getStatusCode()
        ];

        return new JsonResponse($responseData);
    }

    /**
     * Handles renaming of the folder via ajax call
     *
     * @Route("/files/actions/rename-folder", name="action_rename_subdirectory", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws \Exception
     */
    public function renameFolderByPostRequest(Request $request)
    {
        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_UPLOAD_MODULE_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_UPLOAD_MODULE_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_NEW_NAME;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        $newName         = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_NEW_NAME);
        $uploadModuleDir = $request->request->get(FileUploadController::KEY_UPLOAD_MODULE_DIR);
        $currentDirectoryPathInModuleUploadDir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);

        $response = $this->controllers->getFilesUploadSettingsController()->renameSubdirectory($uploadModuleDir, $currentDirectoryPathInModuleUploadDir, $newName);
        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/files/actions/move-or-copy-data-between-folders", name="actions_move_or_copy_data_between_folders", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function moveOrCopyDataBetweenFoldersViaPostRequest(Request $request): JsonResponse
    {
        if ( !$request->request->has(FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        if ( !$request->request->has(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR;
            return AjaxResponse::buildJsonResponseForAjaxCall($message, Response::HTTP_BAD_REQUEST);
        }

        $currentUploadModuleDir = $request->request->get(FilesHandler::KEY_CURRENT_UPLOAD_MODULE_DIR);
        $targetUploadModuleDir  = $request->request->get(FilesHandler::KEY_TARGET_MODULE_UPLOAD_DIR);

        $currentDirectoryPathInModuleUploadDir = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_CURRENT_PATH_IN_MODULE_UPLOAD_DIR);
        $targetDirectoryPathInModuleUploadDir  = $request->request->get(FileUploadController::KEY_SUBDIRECTORY_TARGET_PATH_IN_MODULE_UPLOAD_DIR);

        $doMoveEntireFolder = Utils::getBoolRepresentationOfBoolString($request->request->get(UploadSubdirectoryCopyDataType::KEY_MOVE_FOLDER, false));

        try{
            if( $doMoveEntireFolder ){
                $currentFolderPath = $currentDirectoryPathInModuleUploadDir;
                $targetFolderPath  = $targetDirectoryPathInModuleUploadDir;

                //if not main folder then add upload dir
                if( !$this->isMainUploadDirectory($currentDirectoryPathInModuleUploadDir) ){
                    $currentFolderPath =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $currentUploadModuleDir . DIRECTORY_SEPARATOR . $currentDirectoryPathInModuleUploadDir;
                }

                //if not main folder then add upload dir
                if( !$this->isMainUploadDirectory($targetDirectoryPathInModuleUploadDir) ){
                    $targetFolderPath  =  Env::getUploadDir() . DIRECTORY_SEPARATOR . $targetUploadModuleDir . DIRECTORY_SEPARATOR . $targetDirectoryPathInModuleUploadDir;
                }

                $response     = $this->directoriesHandler->moveDirectory($currentFolderPath, $targetFolderPath);
                $ajaxResponse = AjaxResponse::initializeFromResponse($response);
                if( empty($ajaxResponse->getRouteUrl()) ){
                    $targetUrl = $this->getRouteUrlRedirectForMovingFolder(
                        $request,
                        $currentDirectoryPathInModuleUploadDir,
                        $targetDirectoryPathInModuleUploadDir
                    );

                    if( !empty($targetUrl) ){
                        $ajaxResponse->setRouteUrl($targetUrl);
                    }
                }

            }else{
                /**
                 * In this case files are copied between directories, some actions are skipped here, for example:
                 * - moduleData is not being copied
                 */
                $response = $this->filesHandler->copyData(
                    $currentUploadModuleDir, $targetUploadModuleDir, $currentDirectoryPathInModuleUploadDir, $targetDirectoryPathInModuleUploadDir
                );

                $ajaxResponse = AjaxResponse::initializeFromResponse($response);
            }

        }catch(\Exception | TypeError $e ){

            $this->app->logger->critical("Exception was thrown while calling folders data transfer logic", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
                "exceptionTrace"   => $e->getTraceAsString(),
            ]);

            $message = $this->app->translator->translate('messages.general.internalServerError');
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * Will return the route that will be used to redirect/reload after the folder has been moved
     *
     * @param Request $request
     * @param string $currentDirectoryPathInModuleUploadDir
     * @param string $targetDirectoryPathInModuleUploadDir
     * @return string|null
     * @throws \Exception
     */
    private function getRouteUrlRedirectForMovingFolder(Request $request, string $currentDirectoryPathInModuleUploadDir, string $targetDirectoryPathInModuleUploadDir): ?string
    {
        if( !$request->request->has(FilesHandler::KEY_URL_CALLED_FROM) ){
            return null;
        }

        $urlCalledFrom                 = urldecode($request->request->get(FilesHandler::KEY_URL_CALLED_FROM));
        $routeForOverviewPage          = $this->urlMatcherService->getRouteForUploadBasedModuleUrlOverviewPage($urlCalledFrom);
        $targetSubdirectoryPath        = ( $this->isMainUploadDirectory($targetDirectoryPathInModuleUploadDir) ? null : $targetDirectoryPathInModuleUploadDir ); //if main folder then subdirectory is null
        $encodedTargetSubdirectoryPath = ( is_null($targetSubdirectoryPath) ? null : urlencode($targetSubdirectoryPath) );
        $encodedTargetSubdirectoryPath = $this->normalizeCharactersInStringForUrl($encodedTargetSubdirectoryPath);

        if(
                !empty($routeForOverviewPage)
            &&  $this->urlMatcherService->isUrlEqualToAnyUploadModuleOverviewPage($urlCalledFrom, $currentDirectoryPathInModuleUploadDir)
        ){
            $targetEncodedSubdirectoryPath = $encodedTargetSubdirectoryPath . urlencode(DIRECTORY_SEPARATOR . basename($currentDirectoryPathInModuleUploadDir));
            $urlForTargetSubdirectoryPath  = $this->generateUrl($routeForOverviewPage, [
                UrlMatcherService::ROUTE_PARAM_ENCODED_SUBDIRECTORY_PATH => $targetEncodedSubdirectoryPath,
            ]);

            $normalizedUrlForTargetSubdirectoryPath = $this->normalizeCharactersInStringForUrl($urlForTargetSubdirectoryPath);
            return $normalizedUrlForTargetSubdirectoryPath;
        }

        return $urlCalledFrom;
    }

    /**
     * Will check if given directory in module upload dir is actually main directory or not
     *
     * @param string $directoryPathInModuleUploadDir
     * @return bool
     */
    private function isMainUploadDirectory(string $directoryPathInModuleUploadDir): bool
    {
        $uploadDirs = Env::getUploadDirs();
        return in_array($directoryPathInModuleUploadDir, $uploadDirs);
    }

    /**
     * Now the problem is that if folder has name with space-bar, then any of the urlencoded methods replaces it with special character
     * this then passed to the route generator replaces that character as well - so this causes issues with front
     * navigation where (+) is replaced twice by symfony routing logic
     */
    private function normalizeCharactersInStringForUrl(string $urlString): string
    {
        $normalizedString = str_replace("+", " ", $urlString);
        return $normalizedString;
    }

}