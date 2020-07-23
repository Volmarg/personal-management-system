/**
 * This file handles calling dialogs
 *  Keep in mind that some actions are handled explicitly here due to:
 *  - special logic that must be handled for given call,
 *  - some function were created before building more automatic mechanism with html data attr. utilization
 */
import AjaxResponseDto from "../../DTO/AjaxResponseDto";
import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";
import Selectize       from "../../libs/selectize/Selectize";
import Loader          from "../../libs/loader/Loader";

var bootbox = require('bootbox');

export default (function () {

    if (typeof window.dialogs === 'undefined') {
        window.dialogs = {};
    }

    dialogs.ui = {

        selectors: {
            ids: {
                targetUploadModuleDirInput  : '#move_single_file_target_upload_module_dir',
                targetSubdirectoryTypeInput : '#move_single_file_target_subdirectory_path',
                systemLockPasswordInput     : '#system_lock_resources_password_systemLockPassword'
            },
            classes: {
                fileTransferButton      : '.file-transfer',
                filesTransferButton     : '.files-transfer',
                bootboxModalMainWrapper : '.modal-dialog'
            },
            other: {
                updateTagsInputWithTags: 'form[name^="update_tags"] input.tags'
            }
        },
        data: {
            requestMethod     : "data-dialog-call-request-method",
            requestUrl        : "data-dialog-call-request-url",
            getParameters     : "data-dialog-call-request-get-parameters",
            postParameters    : "data-dialog-call-request-post-parameters",
            callDialogOnClick : "data-call-dialog-on-click",
            callback          : "data-call-dialog-callback"
        },
        placeholders: {
            fileName            : "%fileName%",
            targetUploadType    : "%currentUploadType%",
            noteId              : "%noteId%",
            categoryId          : "%categoryId%"
        },
        messages: {
            doYouReallyWantToMoveSelectedFiles: "Do You really want to move selected files?"
        },
        methods: {
            moveSingleFile                         : '/files/action/move-single-file',
            moveMultipleFiles                      : '/files/action/move-multiple-files',
            updateTagsForMyImages                  : '/api/my-images/update-tags',
            getDataTransferDialogTemplate          : '/dialog/body/data-transfer',
            getTagsUpdateDialogTemplate            : '/dialog/body/tags-update',
            getNotePreviewDialogTemplate           : '/dialog/body/note-preview/%noteId%/%categoryId%',
            systemLockResourcesDialogTemplate      : '/dialog/body/system-lock-resources',
            createSystemLockPasswordDialogTemplate : '/dialog/body/create-system-lock-password',
        },
        vars: {
            fileCurrentPath     : '',
            filesCurrentPaths   : '',
            tags                : ''
        },
        bootstrapNotify: new BootstrapNotify(),
        selectize      : new Selectize(),
        general: {
            init: function(){
                this.attachCallDialogOnClickEvent();
            },
            attachCallDialogOnClickEvent: function(){
                let elements = $("[" + dialogs.ui.data.callDialogOnClick + "=true]");
                let _this    = this;

                elements.on('click', function(event){
                    let $clickedElement = $(event.currentTarget);

                    let requestMethod  = $clickedElement.attr(dialogs.ui.data.requestMethod);
                    let requestUrl     = $clickedElement.attr(dialogs.ui.data.requestUrl);
                    let getParameters  = $clickedElement.attr(dialogs.ui.data.getParameters);
                    let postParameters = $clickedElement.attr(dialogs.ui.data.postParameters);
                    let callbackString = $clickedElement.attr(dialogs.ui.data.callback);
                    let callback       = new Function(callbackString);

                    let usedParameters = null;
                    let url            = null;
                    let data           = null;

                    switch( requestMethod ){
                        case "POST":
                        {
                            if(
                                    ""          === postParameters
                                ||  "undefined" === typeof postParameters
                            ){
                                throw{
                                    "message": "Post parameters are missing for dialog call"
                                }
                            }

                            usedParameters = postParameters;
                            url            = requestUrl;
                            data           = JSON.parse(postParameters);
                        }
                        break;

                        case "GET":
                        {
                            usedParameters = getParameters;
                            data           = null;

                            let getJsonParams  = JSON.parse(getParameters);
                            let urlParams      = new URLSearchParams(getJsonParams).toString();
                            url                = requestUrl + '?' + urlParams;
                        }
                        break;
                        default:
                            throw {
                                "message" : "Unsupported method",
                                "method"  : requestMethod
                            }
                    }

                    Loader.toggleLoader();
                    $.ajax({
                        method : requestMethod,
                        url    : url,
                        data   : data
                    }).always((data) => {
                        dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDialog);
                    })
                });
            },
            /**
             * General function for calling the modal
             * @param url {string}
             * @param method {string}
             * @param requestData {object}
             * @param callback {function}
             */
            buildDialogBody: function(url, method, requestData, callback) {

                if( "undefined" === typeof callback ){
                    callback = null;
                }

                let _this = this;

                Loader.toggleLoader();
                $.ajax({
                    method: method,
                    url: url,
                    data: requestData
                }).always((data) => {
                    dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDialog);
                })
            },
            /**
             * Call the dialog and insert template in it's body
             * @param template {string}
             * @param callback {function}
             */
            callDialog: function (template, callback = null) {

                let dialog = bootbox.alert({
                    size: "large",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {

                    if( "function" === typeof callback ){
                        callback();
                    }

                    ui.forms.init();
                });

            },
        },
        dataTransfer: {
            /**
             *
             * @param filesCurrentPaths array
             * @param moduleName string
             * @param callback function
             */
            buildDataTransferDialog: function (filesCurrentPaths, moduleName, callback = null) {
                dialogs.ui.vars.filesCurrentPaths = filesCurrentPaths;
                let _this = dialogs.ui.dataTransfer;
                let getDataTransferDialogTemplate = dialogs.ui.methods.getDataTransferDialogTemplate;

                let data = {
                    'files_current_locations' : filesCurrentPaths,
                    'moduleName'              : moduleName
                };

                Loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: getDataTransferDialogTemplate,
                    data: data
                }).always((data) => {
                    dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callDataTransferDialog)
                })
            },
            callDataTransferDialog: function (template, callback = null) {

                let _this  = dialogs.ui.dataTransfer;

                let dialog = bootbox.alert({
                    size: "medium",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {
                    let modalMainWrapper = $(dialogs.ui.selectors.classes.bootboxModalMainWrapper);
                    let form             = $(modalMainWrapper).find('form');
                    let formSubmitButton = $(form).find("[type^='submit']");

                    _this.attachDataTransferToDialogFormSubmit(formSubmitButton, callback);
                    ui.forms.init();
                });
            },
            attachDataTransferToDialogFormSubmit: function (button, callback = null){
                let _this = this;
                $(button).on('click', (event) => {
                    event.preventDefault();
                    _this.makeAjaxCallForDataTransfer(callback);
                });
            },
            makeAjaxCallForDataTransfer(callback = null){
                let filesCurrentPaths           = dialogs.ui.vars.filesCurrentPaths;
                let targetUploadModuleDirInput  = $(dialogs.ui.selectors.ids.targetUploadModuleDirInput).val();
                let targetSubdirectoryPath      = $(dialogs.ui.selectors.ids.targetSubdirectoryTypeInput).val();

                let data = {
                    'files_current_locations'                       : filesCurrentPaths,
                    'target_upload_module_dir'                      : targetUploadModuleDirInput,
                    'subdirectory_target_path_in_module_upload_dir' : targetSubdirectoryPath
                };
                Loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url:dialogs.ui.methods.moveMultipleFiles,
                    data: data
                }).always( (data) => {
                    Loader.toggleLoader();

                    let ajaxResponseDto = AjaxResponseDto.fromArray(data);
                    let notifyType      = '';

                    if( ajaxResponseDto.isSuccessCode() ){

                        if( 'function' === typeof(callback) ){
                            callback();
                            bootbox.hideAll()
                        }

                        notifyType = 'success'
                    }else{
                        notifyType = 'danger';
                    }

                    // not checking if code is set because if message is then code must be also
                    if( ajaxResponseDto.isMessageSet() ){
                        dialogs.ui.bootstrapNotify.notify(ajaxResponseDto.message, notifyType);
                    }

                    if( ajaxResponseDto.reloadPage ){
                        if( !ajaxResponseDto.isReloadMessageSet() ){
                            dialogs.ui.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                        }
                        location.reload();
                    }
                })
            },
        },
        tagManagement: {
            buildTagManagementDialog: function (fileCurrentPath, moduleName, callback = null) {
                dialogs.ui.vars.fileCurrentPath = fileCurrentPath;
                let _this = dialogs.ui.tagManagement;
                let getDialogTemplate = dialogs.ui.methods.getTagsUpdateDialogTemplate;

                let data = {
                    'fileCurrentPath': fileCurrentPath,
                    'moduleName'     : moduleName
                };
                Loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: getDialogTemplate,
                    data: data
                }).always((data) => {
                    dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callTagManagementDialog)
                })
            },
            callTagManagementDialog: function (template, callback = null) {

                let _this  = dialogs.ui.tagManagement;

                let dialog = bootbox.alert({
                    size: "medium",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {
                    let modalMainWrapper = $(dialogs.ui.selectors.classes.bootboxModalMainWrapper);
                    let form             = $(modalMainWrapper).find('form');
                    let formSubmitButton = $(form).find("[type^='submit']");

                    _this.attachTagsUpdateOnFormSubmit(formSubmitButton, form, callback);
                    _this.selectize.applyTagsSelectize();
                    ui.forms.init();
                });
            },
            attachTagsUpdateOnFormSubmit: function(button, form, callback = null){
                let _this = this;
                $(button).on('click', (event) => {

                    let formValidity = $(form)[0].checkValidity();

                    if( !formValidity ){
                        $(form)[0].reportValidity();
                        return;
                    }

                    event.preventDefault();
                    _this.makeAjaxCallForTagsUpdate(callback);
                });
            },
            makeAjaxCallForTagsUpdate: function(callback = null){

                let fileCurrentPath = dialogs.ui.vars.fileCurrentPath.replace("/", "");
                let tagsInput       = $(dialogs.ui.selectors.other.updateTagsInputWithTags);
                let tags            = $(tagsInput).val();

                let data = {
                    'tags'              : tags,
                    'fileCurrentPath'   : fileCurrentPath,
                };
                Loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: dialogs.ui.methods.updateTagsForMyImages,
                    data: data
                }).always( (data) => {
                    Loader.toggleLoader();

                    let ajaxResponseDto = AjaxResponseDto.fromArray(data);
                    let notifyType      = '';

                    if( ajaxResponseDto.isSuccessCode() ){

                        if( 'function' === typeof(callback) ){
                            callback(tags);
                            bootbox.hideAll()
                        }

                        notifyType = 'success'
                    }else{
                        notifyType = 'danger';
                    }

                    // not checking if code is set because if message is then code must be also
                    if( ajaxResponseDto.isMessageSet() ){
                        dialogs.ui.bootstrapNotify.notify(ajaxResponseDto.message, notifyType);
                    }

                    if( ajaxResponseDto.reloadPage ){
                        if( ajaxResponseDto.isReloadMessageSet() ){
                            dialogs.ui.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                        }
                        location.reload();
                    }
                })
            }
        },
        notePreview: {
            buildTagManagementDialog: function (noteId, categoryId, callback = null) {
                let _this = dialogs.ui.notePreview;
                let that  = dialogs.ui;
                let getDialogTemplate = dialogs.ui.methods.getNotePreviewDialogTemplate;

                let url = getDialogTemplate.replace(that.placeholders.categoryId, categoryId);
                    url = url.replace(that.placeholders.noteId, noteId);

                Loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callNotePreviewDialog)
                })
            },
            callNotePreviewDialog: function (template, callback = null) {

                let _this  = this;

                let dialog = bootbox.alert({
                    size: "large",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {
                    tinymce.custom.init();
                });
            },
        },
        systemLock:{
            /**
             * Build dialog with confirmation about setting/removing lock for entire system
             * @param callback   {function}
             * @param isUnlocked {boolean}
             */
            buildSystemToggleLockDialog: function (callback = null, isUnlocked) {
                let _this = dialogs.ui.systemLock;
                let url   = dialogs.ui.methods.systemLockResourcesDialogTemplate;

                Loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    Loader.toggleLoader();

                    let reloadPage    = data['reload_page'];
                    let reloadMessage = data['reload_message'];

                    if( undefined !== data['template'] ){
                        _this.callSystemToggleLockDialog(data['template'], callback, isUnlocked);
                    } else if( undefined !== data['errorMessage'] ) {
                        dialogs.ui.bootstrapNotify.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        dialogs.ui.bootstrapNotify.notify(message, 'danger');
                    }

                    if( reloadPage ){
                        if( "" !== reloadMessage ){
                            dialogs.ui.bootstrapNotify.showBlueNotification(reloadMessage);
                        }
                        location.reload();
                    }
                })
            },
            /**
             * Call dialog with confirmation about setting/removing lock for entire system
             * @param template   {string}
             * @param callback   {function}
             * @param isUnlocked {boolean}
             */
            callSystemToggleLockDialog: function (template, callback = null, isUnlocked) {

                let dialog = bootbox.alert({
                    size: "large",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {
                    let $systemLockPasswordInput  = $(dialogs.ui.selectors.ids.systemLockPasswordInput);
                    let $form                     = $systemLockPasswordInput.closest('form');
                    let $systemLockPasswordSubmit = $form.find('button');

                    setTimeout( () => {
                        $systemLockPasswordInput[0].focus();
                    }, 500);

                    $systemLockPasswordSubmit.on('click', function (event) {
                        event.preventDefault();
                        let password = $systemLockPasswordInput.val();
                        ui.lockedResource.ajaxToggleSystemLock(password, isUnlocked);
                    })
                });
            },
            /**
             * Build dialog for creating first time lock password
             * @param callback {function}
             */
            buildCreateLockPasswordForSystemDialog: function (callback = null) {
                let _this = dialogs.ui.systemLock;
                let url   = dialogs.ui.methods.createSystemLockPasswordDialogTemplate;

                Loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    dialogs.ui.handleCommonAjaxCallLogicForBuildingDialog(data, callback, _this.callCreateLockPasswordForSystemDialog);
                })
            },
            /**
             * Calls dialog for creating first time lock password
             * @param callback {function}
             * @param template {string}
             */
            callCreateLockPasswordForSystemDialog: function (template, callback = null) {

                let dialog = bootbox.alert({
                    size: "large",
                    backdrop: true,
                    closeButton: false,
                    message: template,
                    buttons: {
                        ok: {
                            label: 'Cancel',
                            className: 'btn-primary dialog-ok-button',
                            callback: () => {}
                        },
                    },
                    callback: function () {
                    }
                });

                dialog.init( () => {
                    let $systemLockCreatePasswordInput  = $(dialogs.ui.selectors.ids.systemLockPasswordInput);
                    let $form                           = $systemLockCreatePasswordInput.closest('form');
                    let $systemLockCreatePasswordSubmit = $form.find('button');

                    setTimeout( () => {
                        $systemLockCreatePasswordInput[0].focus();
                    }, 500);

                    $systemLockCreatePasswordSubmit.on('click', function (event) {
                        event.preventDefault();
                        let password = $systemLockCreatePasswordInput.val();
                        ui.lockedResource.ajaxCreateLockPasswordForSystem(password);
                    })
                });
            },
        },
        handleCommonAjaxCallLogicForBuildingDialog: function(data, callback, callDialogCallback){
            Loader.toggleLoader();

            try{
                var ajaxResponseDto = AjaxResponseDto.fromArray(data);
            }catch(Exception){
                throw{
                    "message"   : "Unable to build AjaxResponseDto from response data",
                    "exception" : Exception,
                }
            }

            if( ajaxResponseDto.isTemplateSet() ){
                callDialogCallback(ajaxResponseDto.template, callback);
            } else if( !ajaxResponseDto.success) {
                dialogs.ui.bootstrapNotify.notify(ajaxResponseDto.message, 'danger');
            }else{
                let message = 'Something went wrong while trying to load dialog template.';
                dialogs.ui.bootstrapNotify.notify(message, 'danger');
            }

            if( ajaxResponseDto.reloadPage ){
                if( ajaxResponseDto.isReloadMessageSet() ){
                    dialogs.ui.bootstrapNotify.showBlueNotification(ajaxResponseDto.reloadMessage);
                }
                location.reload();
            }
        }

    };

}());
