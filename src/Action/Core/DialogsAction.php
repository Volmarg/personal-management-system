<?php

namespace App\Action\Core;

use App\Action\Files\FileUploadAction;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Utils;
use App\Entity\System\LockedResource;
use App\Form\Modules\Contacts\MyContactTypeDtoType;
use App\Form\Modules\Issues\MyIssueContactType;
use App\Form\Modules\Issues\MyIssueProgressType;
use App\Form\Modules\Todo\MyTodoType;
use App\Form\System\SystemLockResourcesPasswordType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class is only responsible for building dialogs data in response for example on Ajax call
 * Class Dialogs
 * @package App\Controller\Utils
 */
class DialogsAction extends AbstractController
{
    const TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD = 'page-elements/components/dialogs/bodies/edit-create-contact-card.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER           = 'page-elements/components/dialogs/bodies/files-transfer.html.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_REMOVE_FILES             = 'page-elements/components/dialogs/bodies/remove-files.html.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS              = 'page-elements/components/dialogs/bodies/update-tags.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER               = 'page-elements/components/dialogs/bodies/new-folder.twig';
    const TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_RESOURCES         = 'page-elements/components/dialogs/bodies/system-lock-resources.twig';
    const TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_CREATE_PASSWORD   = 'page-elements/components/dialogs/bodies/system-lock-create-password.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_PREVIEW_ISSUE_DETAILS    = 'page-elements/components/dialogs/bodies/preview-issue-details.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_ADD_ISSUE_DATA           = 'page-elements/components/dialogs/bodies/add-issue-data.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPDATE_ISSUE             = 'page-elements/components/dialogs/bodies/update-issue.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_CREATE_ISSUE             = 'page-elements/components/dialogs/bodies/create-issue.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPLOAD_MODULE_SETTINGS   = 'page-elements/components/dialogs/bodies/upload-modules/settings.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_CREATE_NOTE              = 'page-elements/components/dialogs/bodies/create-note.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_FILES_UPLOAD             = 'page-elements/components/dialogs/bodies/upload.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_ADD_OR_MODIFY_TODO       = 'page-elements/components/dialogs/bodies/add-or-modify-todo.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_FIRST_LOGIN_INFORMATION  = 'page-elements/components/dialogs/bodies/first-login-information.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_EDIT_TRAVEL_IDEA         = 'page-elements/components/dialogs/bodies/edit-travel-idea.twig';
    const TWIG_TEMPLATE_NOTE_EDIT_MODAL                      = 'modules/my-notes/components/note-edit-modal-body.html.twig';
    const TWIG_TEMPLATE_UNAUTHORIZED_ACCESS                  = 'page-elements/components/dialogs/bodies/unauthorized-access.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPDATE_BILL              = 'page-elements/components/dialogs/bodies/update-bill.twig';
    const KEY_FILE_CURRENT_PATH                              = 'fileCurrentPath';
    const KEY_MODULE_NAME                                    = 'moduleName';
    const KEY_ENTITY_ID                                      = "entityId";
    const KEY_ACTION_PATHNAME                                = 'actionPathname';
    const KEY_IS_UPDATE_ACTION                               = 'isUpdateAction';
    const KEY_RELOADED_MENU_NODE                             = 'reloadedMenuNode';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var FileTagger $fileTagger
     */
    private FileTagger $fileTagger;

    /**
     * @var DirectoriesHandler $directoriesHandler
     */
    private DirectoriesHandler $directoriesHandler;

    /**
     * @var FileUploadAction $fileUploadAction
     */
    private FileUploadAction $fileUploadAction;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(
        Application        $app,
        FileTagger         $fileTagger,
        DirectoriesHandler $directoriesHandler,
        FileUploadAction   $fileUploadAction,
        Controllers        $controllers
    ) {
        $this->app                = $app;
        $this->fileTagger         = $fileTagger;
        $this->controllers        = $controllers;
        $this->directoriesHandler = $directoriesHandler;
        $this->fileUploadAction   = $fileUploadAction;
    }

    /**
     * @Route("/dialog/body/data-transfer", name="dialog_body_data_transfer", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildDataTransferDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{

            if( !$request->request->has(FilesHandler::KEY_FILES_CURRENT_PATHS) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_FILES_CURRENT_PATHS;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            if( !$request->request->has(static::KEY_MODULE_NAME) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MODULE_NAME;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $moduleName = $request->request->get(static::KEY_MODULE_NAME);

            if( !array_key_exists($moduleName, $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule()) ){
                $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            // in ligthgallery.html.twig
            $filesCurrentPaths = $request->request->get(FilesHandler::KEY_FILES_CURRENT_PATHS);

            //check if any of the files path is invalid
            foreach($filesCurrentPaths as $fileCurrentPath){

                $file = new File($fileCurrentPath);

                if( !$file->isFile() ){
                    $message = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');

                    $ajaxResponse->setMessage($message);
                    $ajaxResponse->setSuccess(false);
                    $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                    $jsonResponse = $ajaxResponse->buildJsonResponse();
                    return $jsonResponse;
                }
            }

            $allUploadBasedModules = $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule();

            $formData = [
                FilesHandler::KEY_MODULES_NAMES => $allUploadBasedModules
            ];

            $form = $this->app->forms->moveSingleFileForm($formData); //todo: change name to moveFiles

            $templateData = [
                'form'                 => $form->createView(),
                'transferredFilesJson' => json_encode($filesCurrentPaths),
            ];

            $template = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/tags-update", name="dialog_body_tags_update", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildTagsUpdateDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(static::KEY_FILE_CURRENT_PATH) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_CURRENT_PATH;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $moduleName = $request->request->get(static::KEY_MODULE_NAME);
            if( !array_key_exists($moduleName, $this->controllers->getFileUploadController()->getUploadModulesDirsForNonLockedModule()) ){
                $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            // for example in: ligthgallery.html.twig
            $fileCurrentPath = FilesHandler::trimFirstAndLastSlash($request->request->get(static::KEY_FILE_CURRENT_PATH));
            $file = new File($fileCurrentPath);

            if( !$file->isFile() ){
                $message = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $this->fileTagger->prepare([],$fileCurrentPath);
            $fileTags = $this->controllers->getFilesTagsController()->getFileTagsEntityByFileFullPath($fileCurrentPath);
            $tagsJson = ( !is_null($fileTags) ? $fileTags->getTags() : '');

            $formData  = [
                FileTagger::KEY_TAGS => $tagsJson
            ];
            $form           = $this->app->forms->updateTagsForm($formData);
            $isUpdateAction = Utils::getBoolRepresentationOfBoolString($request->request->get(self::KEY_IS_UPDATE_ACTION, false));

            $templateData = [
                'form'                  => $form->createView(),
                'file_current_location' => $fileCurrentPath,
                'is_update_action'      => $isUpdateAction,
            ];

            $template = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/create-folder", name="dialog_body_create_folder", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function buildCreateNewFolderDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{

            if( !$request->request->has(static::KEY_MODULE_NAME) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MODULE_NAME;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $moduleName = $request->request->get(static::KEY_MODULE_NAME);

            $createSubdirForm = $this->app->forms->uploadCreateSubdirectoryForm();

            $templateData = [
                'form'                    => $createSubdirForm->createView(),
                'menu_node_module_name'   => $moduleName
            ];

            $template = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/upload", name="dialog_body_upload", methods="POST")
     * @return JsonResponse
     * @throws Exception
     */
    public function buildUploadDialogBody(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{

            $template = $this->fileUploadAction->renderFineUploadTemplate(false, self::TWIG_TEMPLATE_DIALOG_BODY_FILES_UPLOAD)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/create-note/{category}/{categoryId}", name="dialog_body_create_note", methods="POST")
     * @param Request $request
     * @param string $category
     * @param string $categoryId
     * @return JsonResponse
     */
    public function buildCreateNoteDialogBody(Request $request, string $category, string $categoryId): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $formView = $this->app->forms->noteTypeForm()->createView();

            $templateData = [
                'form' => $formView
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_CREATE_NOTE, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/note-preview/{noteId}/{categoryId}", name="dialog_body_preview_note", methods="GET")
     * @param string $noteId
     * @param string $categoryId
     * @return JsonResponse
     *
     */
    public function buildPreviewNoteDialogBody(string $noteId, string $categoryId): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $note = $this->controllers->getMyNotesController()->getOneById($noteId);

            if( is_null($note) ){
                $message = $this->app->translator->translate('responses.notes.couldNotFindNoteForId') . $noteId;
                return new JsonResponse([
                    'message' => $message
                ]);
            }

            $templateData = [
                'note'          => $note,
                'category_id'   => $categoryId,
                'no_delete'     => true,
                'no_close'      => true,
            ];

            $template = $this->render(self::TWIG_TEMPLATE_NOTE_EDIT_MODAL, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/create-contact-card", name="dialog_body_create_contact_card", methods="POST")
     * @return JsonResponse
     */
    public function buildCreateContactCardDialogBody(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $contactForm  = $this->app->forms->contactForm();

            $templateData = [
                'contact_form'  => $contactForm->createView(),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/system-lock-resources", name="dialog_body_system_lock_resources", methods="GET")
     * @return JsonResponse
     */
    public function buildSystemLockResourcesDialogBody(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $passwordForm  = $this->app->forms->systemLockResourcesPasswordForm([
                SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => false
            ]);

            $templateData = [
                'password_form'  => $passwordForm->createView(),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_RESOURCES, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/create-system-lock-password", name="dialog_body_create_system_lock_password", methods="GET")
     * @return JsonResponse
     */
    public function buildCreateSystemLockPasswordDialogBody(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $passwordForm  = $this->app->forms->systemLockResourcesPasswordForm([
                SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => true
            ]);

            $templateData = [
                'password_form'  => $passwordForm->createView(),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_CREATE_PASSWORD, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/edit-contact-card", name="dialog_body_edit_contact_card", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function buildEditContactCardDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $entityId      = $request->request->get(self::KEY_ENTITY_ID);
            $contact       = $this->controllers->getMyContactController()->findOneById($entityId);
            $formsRenders  = [];

            if( is_null($contact) ){
                $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");

                $ajaxResponse->setMessage($message . $entityId);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $contactTypesDtos = $contact->getContacts()->getContactTypeDtos();

            foreach( $contactTypesDtos as $contactTypeDto ){
                $options = [
                    MyContactTypeDtoType::KEY_NAME => $contactTypeDto->getDetails(),
                    MyContactTypeDtoType::KEY_TYPE => $contactTypeDto->getName()
                ];

                $formsRenders[] = $this->app->forms->getFormViewWithoutFormTags(MyContactTypeDtoType::class, $options);
            }

            $contactForm = $this->app->forms->contactForm([], $contact);

            $templateData = [
                'edit_contact_card'  => true,
                'contact_types_dtos' => $contactTypesDtos, //todo - need to append few type forms with dto data
                'contact_form'       => $contactForm->createView(),
                'subforms'           => $formsRenders
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD, $templateData)->getContent();

        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/preview-issue-details", name="dialog_body_preview_issue_details", methods="POST")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function buildPreviewIssueDetailsDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $entityId = $request->request->get(self::KEY_ENTITY_ID);
            $issue    = $this->controllers->getMyIssuesController()->findIssueById($entityId);

            if( is_null($issue) ){
                $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");

                $ajaxResponse->setMessage($message . $entityId);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $issueCardDto = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues([$issue]);

            $templateData = [
                'issueCardDto' => reset($issueCardDto),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_PREVIEW_ISSUE_DETAILS, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/add-issue-data", name="dialog_body_add_issue_data", methods="POST")
     * @param Request $request
     * @return JsonResponse
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildAddIssueDataDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $entityId = $request->request->get(self::KEY_ENTITY_ID);
            $issue    = $this->controllers->getMyIssuesController()->findIssueById($entityId);

            if( is_null($issue) ){
                $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");

                $ajaxResponse->setMessage($message . $entityId);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $issueCardDto = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues([$issue]);
            $progressForm = $this->app->forms->issueProgressForm([MyIssueProgressType::OPTION_ENTITY_ID => $entityId])->createView();
            $contactForm  = $this->app->forms->issueContactForm([MyIssueContactType::OPTION_ENTITY_ID   => $entityId])->createView();

            $templateData = [
                'issueCardDto' => reset($issueCardDto),
                'progressForm' => $progressForm,
                'contactForm'  => $contactForm,
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_ADD_ISSUE_DATA, $templateData)->getContent();

        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/update-issue", name="dialog_body_update_issue", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function buildUpdateIssueDataDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $entityId = $request->request->get(self::KEY_ENTITY_ID);
            $issue    = $this->controllers->getMyIssuesController()->findIssueById($entityId);

            if( is_null($issue) ){
                $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");

                $ajaxResponse->setMessage($message . $entityId);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $templateData = [
                'issue_form' => $this->app->forms->issueForm([], $issue)->createView(),
                'id'         => $entityId,
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_UPDATE_ISSUE, $templateData)->getContent();

        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/update-bill", name="dialog_body_update_bill", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function buildUpdateBillDataDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $entityId = $request->request->getInt(self::KEY_ENTITY_ID);
            $bill     = $this->controllers->getMyPaymentsBillsController()->findOneById($entityId);

            if( is_null($bill) ){
                $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");

                $ajaxResponse->setMessage($message . $entityId);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $templateData = [
                'bill_form' => $this->app->forms->paymentsBillsForm([], $bill)->createView(),
                'id'        => $entityId,
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_UPDATE_BILL, $templateData)->getContent();

        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/create-issue", name="dialog_body_create_issue", methods="POST")
     * @return JsonResponse
     */
    public function buildCreateIssueDialogBody(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $templateData = [
                'issueForm' => $this->app->forms->issueForm()->createView(),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_CREATE_ISSUE, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/add-or-modify-todo", name="dialog_body_add_or_modify_todo", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function buildAddOrModifyTodoDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        if( !$request->request->has(self::KEY_MODULE_NAME) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_MODULE_NAME;

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
            $jsonResponse = $ajaxResponse->buildJsonResponse();
            return $jsonResponse;
        }

        if( !$request->request->has(self::KEY_ACTION_PATHNAME) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ACTION_PATHNAME;

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
            $jsonResponse = $ajaxResponse->buildJsonResponse();
            return $jsonResponse;
        }

        $entityId       = $request->request->get(self::KEY_ENTITY_ID, "");
        $actionPathname = $request->request->get(self::KEY_ACTION_PATHNAME);
        $moduleName     = $request->request->get(self::KEY_MODULE_NAME);

        $hideDisplayOnDashboard = (
            ModulesController::MODULE_NAME_ISSUES == $moduleName
        );

        try{

            $module = $this->controllers->getModuleController()->getOneByName($moduleName);

            $todo = null;
            if( !empty($entityId) ){
                $todo = $this->controllers->getMyTodoController()->getTodoByModuleNameAndEntityId($moduleName, $entityId);
            }

            if( !$this->controllers->getLockedResourceController()->isAllowedToSeeResource($entityId, LockedResource::TYPE_ENTITY, ModulesController::MODULE_NAME_TODO) ){
                $template = $this->render(self::TWIG_TEMPLATE_UNAUTHORIZED_ACCESS)->getContent();
                $ajaxResponse->setCode(Response::HTTP_UNAUTHORIZED);
                $ajaxResponse->setTemplate($template);
                $ajaxResponse->setSuccess(false);

                return $ajaxResponse->buildJsonResponse();
            }

            $todoForm = $this->app->forms->todoForm([
                MyTodoType::OPTION_PREDEFINED_MODULE => $module
            ]);

            $todoElementForm = $this->app->forms->todoElementForm();

            $templateData = [
                'entity_id'                 => $entityId,
                'todo'                      => $todo,
                'hide_module_select'        => true,
                'hide_display_on_dashboard' => $hideDisplayOnDashboard,
                'todo_form'                 => $todoForm->createView(),
                'todo_element_form'         => $todoElementForm,
                'data_template_url'         => $this->generateUrl($actionPathname),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_ADD_OR_MODIFY_TODO, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/first-login-information", name="dialog_body_first_login_information", methods="POST")
     * @return JsonResponse
     * @throws Exception
     */
    public function buildFirstLoginInformationDialog(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_FIRST_LOGIN_INFORMATION)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/files-removal", name="dialog_body_files_removal", methods="POST")
     * @return JsonResponse
     * @throws Exception
     */
    public function buildFilesRemovalDialog(): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{

            $removedFilesJson = json_encode([]);

            $templateData = [
                'removedFilesJson' =>   $removedFilesJson
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_REMOVE_FILES, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/edit-travel-idea", name="dialog_body_edit_travel_idea", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function buildEditTravelIdeaDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code          = Response::HTTP_OK;
        $template      = "";
        $success       = true;

        try{
            if( !$request->request->has(self::KEY_ENTITY_ID) ){
                $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }
            $categories  = $this->controllers->getMyTravelsIdeasController()->getAllCategories(true);
            $entityId   = $request->request->get(self::KEY_ENTITY_ID);
            $travelIdea = $this->controllers->getMyTravelsIdeasController()->findOneById($entityId);

            $ideaForm = $this->app->forms->travelIdeasForm(['categories' => $categories], $travelIdea);

            $templateData = [
                'idea_form' => $ideaForm->createView(),
                'entity_id' => $entityId
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_EDIT_TRAVEL_IDEA, $templateData)->getContent();

        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

    /**
     * @Route("/dialog/body/upload-module-settings", name="dialog_body_upload_module_settings", methods="POST")
     * @return JsonResponse
     */
    public function buildUploadModuleSettingsDialogBody(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $template     = "";
        $success      = true;

        try{
            $copyDataForm = $this->app->forms->copyUploadSubdirectoryDataForm();

            $templateData = [
                'copy_data_form'     => $copyDataForm->createView(),
                'reloaded_menu_node' => $request->request->get(self::KEY_RELOADED_MENU_NODE, ""),
            ];

            $template = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_UPLOAD_MODULE_SETTINGS, $templateData)->getContent();
        }catch(Exception $e){
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setTemplate($template);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

}
