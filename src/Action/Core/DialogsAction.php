<?php

namespace App\Action\Core;

use App\Action\Files\FileUploadAction;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Files\FileUploadController;
use App\Form\Modules\Contacts\MyContactTypeDtoType;
use App\Form\Modules\Issues\MyIssueContactType;
use App\Form\Modules\Issues\MyIssueProgressType;
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
 * Todo:
 *  at some point JsonResponse should be replaced with AjaxResponse (my class)
 *  - consider adding new key in class if not yet present `success` or `error`
 *  same ajax calls for dialog should be adjusted on front for more future flexibility
 * This class is only responsible for building dialogs data in response for example on Ajax call
 * Class Dialogs
 * @package App\Controller\Utils
 */
class DialogsAction extends AbstractController
{
    const TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD = 'page-elements/components/dialogs/bodies/edit-create-contact-card.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER           = 'page-elements/components/dialogs/bodies/files-transfer.html.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS              = 'page-elements/components/dialogs/bodies/update-tags.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER               = 'page-elements/components/dialogs/bodies/new-folder.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_UPLOAD                   = 'page-elements/components/dialogs/bodies/new-folder.twig';
    const TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_RESOURCES         = 'page-elements/components/dialogs/bodies/system-lock-resources.twig';
    const TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_CREATE_PASSWORD   = 'page-elements/components/dialogs/bodies/system-lock-create-password.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_PREVIEW_ISSUE_DETAILS    = 'page-elements/components/dialogs/bodies/preview-issue-details.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_ADD_ISSUE_DATA           = 'page-elements/components/dialogs/bodies/add-issue-data.twig';
    const TWIG_TEMPLATE_DIALOG_BODY_CREATE_ISSUE             = 'page-elements/components/dialogs/bodies/create-issue.twig';
    const TWIG_TEMPLATE_NOTE_EDIT_MODAL                      = 'modules/my-notes/components/note-edit-modal-body.html.twig';
    const KEY_FILE_CURRENT_PATH                              = 'fileCurrentPath';
    const KEY_MODULE_NAME                                    = 'moduleName';
    const KEY_ENTITY_ID                                      = "entityId";

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var FileTagger $file_tagger
     */
    private $file_tagger;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var FileUploadAction $file_upload_action
     */
    private $file_upload_action;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(
        Application          $app,
        FileTagger           $file_tagger,
        DirectoriesHandler   $directories_handler,
        FileUploadAction     $file_upload_action,
        Controllers          $controllers
    ) {
        $this->app                 = $app;
        $this->file_tagger         = $file_tagger;
        $this->controllers         = $controllers;
        $this->directories_handler = $directories_handler;
        $this->file_upload_action  = $file_upload_action;
    }

    /**
     * @Route("/dialog/body/data-transfer", name="dialog_body_data_transfer", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function buildDataTransferDialogBody(Request $request) {

        if( !$request->request->has(FilesHandler::KEY_FILES_CURRENT_PATHS) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . FilesHandler::KEY_FILES_CURRENT_PATHS;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        if( !$request->request->has(static::KEY_MODULE_NAME) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MODULE_NAME;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $module_name  = $request->request->get(static::KEY_MODULE_NAME);

        if( !array_key_exists($module_name, FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES) ){
            $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        // in ligthgallery.html.twig
        $files_current_paths = $request->request->get(FilesHandler::KEY_FILES_CURRENT_PATHS);

        //check if any of the files path is invalid
        foreach($files_current_paths as $file_current_path){

            $message       = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');
            $response_data = [
                'errorMessage' => $message
            ];

            try{
                $file = new File($file_current_path);

                if( !$file->isFile() ){
                    return new JsonResponse($response_data);
                }
            }catch(Exception $e) {
                return new JsonResponse($response_data);
            }

        }

        $all_upload_based_modules = FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES;

        $form_data  = [
            FilesHandler::KEY_MODULES_NAMES => $all_upload_based_modules
        ];

        $form = $this->app->forms->moveSingleFileForm($form_data); //todo: change name to moveFiles

        $template_data = [
            'form' => $form->createView()
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_FILES_TRANSFER, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }


    /**
     * @Route("/dialog/body/tags-update", name="dialog_body_tags_update", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function buildTagsUpdateDialogBody(Request $request) {

        if( !$request->request->has(static::KEY_FILE_CURRENT_PATH) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_FILE_CURRENT_PATH;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $module_name  = $request->request->get(static::KEY_MODULE_NAME);

        if( !array_key_exists($module_name, FileUploadController::MODULES_UPLOAD_DIRS_FOR_MODULES_NAMES) ){
            $message = $this->app->translator->translate('responses.upload.moduleNameIsIncorrect');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        // in ligthgallery.html.twig
        $file_current_path = FilesHandler::trimFirstAndLastSlash($request->request->get(static::KEY_FILE_CURRENT_PATH));

        $file = new File($file_current_path);

        if( !$file->isFile() ){
            $message = $this->app->translator->translate('responses.files.filePathIsIncorrectFileDoesNotExist');
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $this->file_tagger->prepare([],$file_current_path);
        $file_tags = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_current_path);
        $tags_json = ( !is_null($file_tags) ? $file_tags->getTags() : '');

        $form_data  = [
            FileTagger::KEY_TAGS=> $tags_json
        ];
        $form = $this->app->forms->updateTagsForm($form_data);

        $template_data = [
            'form' => $form->createView()
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_UPDATE_TAGS, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/create-folder", name="dialog_body_create_folder", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function buildCreateNewFolderDialogBody(Request $request) {
        if( !$request->request->has(static::KEY_MODULE_NAME) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MODULE_NAME;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $module_name = $request->request->get(static::KEY_MODULE_NAME);

        $create_subdir_form = $this->app->forms->uploadCreateSubdirectoryForm();

        $template_data = [
          'form'                    => $create_subdir_form->createView(),
          'menu_node_module_name'   => $module_name
        ];

        $rendered_view = $this->render(static::TWIG_TEMPLATE_DIALOG_BODY_NEW_FOLDER, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/upload", name="dialog_body_upload", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function buildUploadDialogBody(Request $request) {
        $template = 'page-elements/components/dialogs/bodies/upload.twig';

        $rendered_template = $this->file_upload_action->renderTemplate(false, $template);

        return $rendered_template;
    }

    /**
     * @Route("/dialog/body/create-note/{category}/{category_id}", name="dialog_body_create_note", methods="POST")
     * @param Request $request
     * @param string $category
     * @param string $category_id
     * @return Response
     */
    public function buildCreateNoteDialogBody(Request $request, string $category, string $category_id) {
        $template = 'page-elements/components/dialogs/bodies/create-note.twig';
        $form_view = $this->app->forms->noteTypeForm()->createView();

        $data = [
            'form' => $form_view
        ];

        return $this->render($template, $data);

    }

    /**
     * @Route("/dialog/body/note-preview/{note_id}/{category_id}", name="dialog_body_preview_note", methods="GET")
     * @param Request $request
     * @param string $note_id
     * @param string $category_id
     * @return Response
     *
     */
    public function buildPreviewNoteDialogBody(Request $request, string $note_id, string $category_id) {

        $note = $this->app->repositories->myNotesRepository->find($note_id);

        if( is_null($note) ){
            $message = $this->app->translator->translate('responses.notes.couldNotFindNoteForId') . $note_id;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $template_data = [
            'note'          => $note,
            'category_id'   => $category_id,
            'no_delete'     => true,
            'no_close'      => true,
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_NOTE_EDIT_MODAL, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);

    }

    /**
     * @Route("/dialog/body/create-contact-card", name="dialog_body_create_contact_card", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function buildCreateContactCardDialogBody(Request $request) {

        $contact_form  = $this->app->forms->contactForm();

        $template_data = [
            'contact_form'  => $contact_form->createView(),
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);

    }

    /**
     * @Route("/dialog/body/system-lock-resources", name="dialog_body_system_lock_resources", methods="GET")
     * @return Response
     */
    public function buildSystemLockResourcesDialogBody() {

        $password_form  = $this->app->forms->systemLockResourcesPasswordForm([
            SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => false
        ]);

        $template_data = [
            'password_form'  => $password_form->createView(),
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_RESOURCES, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);

    }

    /**
     * @Route("/dialog/body/create-system-lock-password", name="dialog_body_create_system_lock_password", methods="GET")
     * @return Response
     */
    public function buildCreateSystemLockPasswordDialogBody() {

        $password_form  = $this->app->forms->systemLockResourcesPasswordForm([
            SystemLockResourcesPasswordType::RESOLVER_OPTION_IS_CREATE_PASSWORD => true
        ]);

        $template_data = [
            'password_form'  => $password_form->createView(),
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_SYSTEM_LOCK_CREATE_PASSWORD, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/edit-contact-card", name="dialog_body_edit_contact_card", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function buildEditContactCardDialogBody(Request $request) {

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $entity_id      = $request->request->get(self::KEY_ENTITY_ID);
        $contact        = $this->app->repositories->myContactRepository->findOneById($entity_id);
        $forms_renders  = [];

        if( is_null($contact) ){
            $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");
            return new JsonResponse([
                'errorMessage' => $message . $entity_id
            ]);
        }

        $contact_types_dtos = $contact->getContacts()->getContactTypeDtos();

        foreach( $contact_types_dtos as $contact_type_dto ){
            $options = [
                MyContactTypeDtoType::KEY_NAME => $contact_type_dto->getDetails(),
                MyContactTypeDtoType::KEY_TYPE => $contact_type_dto->getName()
            ];

            $forms_renders[] = $this->app->forms->getFormViewWithoutFormTags(MyContactTypeDtoType::class, $options);
        }

        $contact_form = $this->app->forms->contactForm([], $contact);

        $template_data = [
            'contact_types_dtos' => $contact_types_dtos, //todo - need to append few type forms with dto data
            'contact_form'       => $contact_form->createView(),
            'subforms'           => $forms_renders
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_EDIT_CREATE_CONTACT_CARD, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/preview-issue-details", name="dialog_body_preview_issue_details", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function buildPreviewIssueDetailsDialogBody(Request $request) {

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $entity_id = $request->request->get(self::KEY_ENTITY_ID);
        $issue     = $this->app->repositories->myIssueRepository->find($entity_id);

        if( is_null($issue) ){
            $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");
            return new JsonResponse([
                'errorMessage' => $message . $entity_id
            ]);
        }

        $issue_card_dto = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues([$issue]);

        $template_data = [
            'issueCardDto' => reset($issue_card_dto),
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_PREVIEW_ISSUE_DETAILS, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/add-issue-data", name="dialog_body_add_issue_data", methods="POST")
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function buildAddIssueDataDialogBody(Request $request) {

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . self::KEY_ENTITY_ID;
            return new JsonResponse([
                'errorMessage' => $message
            ]);
        }

        $entity_id = $request->request->get(self::KEY_ENTITY_ID);
        $issue     = $this->app->repositories->myIssueRepository->find($entity_id);

        if( is_null($issue) ){
            $message = $this->app->translator->translate("messages.general.noEntityWasFoundForId");
            return new JsonResponse([
                'errorMessage' => $message . $entity_id
            ]);
        }

        $issue_card_dto = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues([$issue]);
        $progress_form  = $this->app->forms->issueProgressForm([MyIssueProgressType::OPTION_ENTITY_ID => $entity_id])->createView();
        $contact_form   = $this->app->forms->issueContactForm([MyIssueContactType::OPTION_ENTITY_ID   => $entity_id])->createView();

        $template_data = [
            'issueCardDto' => reset($issue_card_dto),
            'progressForm' => $progress_form,
            'contactForm'  => $contact_form,
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_ADD_ISSUE_DATA, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }

    /**
     * @Route("/dialog/body/create-issue", name="dialog_body_create_issue", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function buildCreateIssueDialogBody(Request $request) {

        $template_data = [
            'issueForm' => $this->app->forms->issueForm()->createView(),
        ];

        $rendered_view = $this->render(self::TWIG_TEMPLATE_DIALOG_BODY_CREATE_ISSUE, $template_data);

        $response_data = [
            'template' => $rendered_view->getContent()
        ];

        return new JsonResponse($response_data);
    }


}
