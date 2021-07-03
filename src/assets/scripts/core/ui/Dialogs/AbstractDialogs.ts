import AjaxResponseDto      from "../../../DTO/AjaxResponseDto";
import BootstrapNotify      from "../../../libs/bootstrap-notify/BootstrapNotify";
import Selectize            from "../../../libs/selectize/Selectize";
import Loader               from "../../../libs/loader/Loader";
import BootboxWrapper       from "../../../libs/bootbox/BootboxWrapper";
import FormsUtils           from "../../utils/FormsUtils";
import DialogLogicLoader    from "./LogicLoader/DialogLogicLoader";
import AjaxEvents           from "../../ajax/AjaxEvents";

/**
 * @description This file handles calling dialogs
 *              Keep in mind that some actions are handled explicitly here due to:
 *              - special logic that must be handled for given call,
 *              - some function were created before building more automatic mechanism with html data attr. utilization
 */
export default abstract class AbstractDialogs {

    /**
     * @type Object
     */
    protected static selectors = {
        ids: {
            targetUploadModuleDirInput  : '#move_single_file_target_upload_module_dir',
            targetSubdirectoryTypeInput : '#move_single_file_target_subdirectory_path',
            systemLockPasswordInput     : '#systemLockDialog #system_lock_resources_password_systemLockPassword'
        },
        classes: {
            fileTransferButton      : '.file-transfer',
            filesTransferButton     : '.files-transfer',
            bootboxModalMainWrapper : '.modal-dialog',
            callWidgetModal         : '.call-widget-modal'
        },
        other: {
            updateTagsInputWithTags: 'form[name^="update_tags"] input.tags'
        },
        classesNames: {
            widgetModalClassName : 'widget-modal',
        }
    };

    /**
     * @type Object
     */
    protected data = {
        requestMethod        : "data-dialog-call-request-method",
        requestUrl           : "data-dialog-call-request-url",
        postParameters       : "data-dialog-call-request-post-parameters",
        callDialogOnClick    : "data-call-dialog-on-click",
        dialogName           : "data-dialog-name",
        transferredFilesJson : "data-transferred-files-json",
        fileCurrentLocation  : "data-tag-update-file-current-location",
    };

    /**
     * @type Object
     */
    protected placeholders = {
        fileName            : "%fileName%",
        targetUploadType    : "%currentUploadType%",
        noteId              : "%noteId%",
        categoryId          : "%categoryId%"
    };

    /**
     * @type Object
     */
    protected messages = {
        doYouReallyWantToMoveSelectedFiles: "Do You really want to move selected files?"
    };

    /**
     * @type Object
     */
    protected methods = {
        moveSingleFile                         : '/files/action/move-single-file',
        moveMultipleFiles                      : '/files/action/move-multiple-files',
        updateTagsForMyImages                  : '/api/my-images/update-tags',
        getDataTransferDialogTemplate          : '/dialog/body/data-transfer',
        getTagsUpdateDialogTemplate            : '/dialog/body/tags-update',
        getNotePreviewDialogTemplate           : '/dialog/body/note-preview/%noteId%/%categoryId%',
        systemLockResourcesDialogTemplate      : '/dialog/body/system-lock-resources',
        createSystemLockPasswordDialogTemplate : '/dialog/body/create-system-lock-password',
    };

    /**
     * @type Object
     */
    protected vars = {
        fileCurrentPath : '',
        tags            : ''
    };

    /**
     * @type BootstrapNotify
     */
    protected bootstrapNotify = new BootstrapNotify();

    /**
     * @type Selectize
     */
    protected selectize = new Selectize();

    /**
     * @type BootboxWrapper
     */
    protected bootbox = new BootboxWrapper();

    /**
     * @type FormsUtils
     */
    protected forms = new FormsUtils();

    /**
     * @type DialogLogicLoader
     */
    protected dialogLogicLoader = new DialogLogicLoader();

    /**
     * @type AjaxEvents
     */
    protected ajaxEvents = new AjaxEvents();

    protected handleCommonAjaxCallLogicForBuildingDialog(data, callback, callDialogCallback, center: boolean = false, dialogButtonLabel:string = null, callbackAfter = null){
        Loader.toggleMainLoader();

        try{
            var ajaxResponseDto = AjaxResponseDto.fromArray(data);
        }catch(Exception){
            throw{
                "message"   : "Unable to build AjaxResponseDto from response data",
                "exception" : Exception,
            }
        }

        if( ajaxResponseDto.isRouteSet() ){
            this.ajaxEvents.loadModuleContentByUrl(ajaxResponseDto.routeUrl)
        }else if( ajaxResponseDto.isTemplateSet() ){
            callDialogCallback(ajaxResponseDto.template, callback, center, dialogButtonLabel, callbackAfter);
        } else if( !ajaxResponseDto.success) {
            this.bootstrapNotify.showRedNotification(ajaxResponseDto.message);
        }else{
            let message = 'Something went wrong while trying to load dialog template.';
            this.bootstrapNotify.showRedNotification(message);
        }

        if( ajaxResponseDto.reloadPage ){
            if( ajaxResponseDto.isReloadMessageSet() ){
                this.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
            }
            location.reload();
        }
    }

}