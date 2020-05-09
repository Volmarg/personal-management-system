/**
 * This file handles calling dialogs
 *  Keep in mind that some actions are handled explicitly here due to:
 *  - special logic that must be handled for given call,
 *  - some function were created before building more automatic mechanism with html data attr. utilization
 */
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

                    ui.widgets.loader.toggleLoader();
                    $.ajax({
                        method : requestMethod,
                        url    : url,
                        data   : data
                    }).always((data) => {
                        ui.widgets.loader.toggleLoader();

                        try{
                            var template     = data['template'];
                            var errorMessage = data['errorMessage'];

                        }catch(Exception){
                            throw{
                                "message"   : "Unable to extract data from ajax response",
                                "exception" : Exception,
                            }
                        }

                        if( undefined !== template ){
                            _this.callDialog(template, callback);
                        } else if(undefined !== errorMessage) {
                            bootstrap_notifications.notify(errorMessage, 'danger');
                        }else{
                            let message = 'Something went wrong while trying to load dialog template.';
                            bootstrap_notifications.notify(message, 'danger');
                        }

                    })

                });
            },
            /**
             * Attaches dialog calling logic for given selector and reads data from it to build dialog
             * @param selector {string}
             * @param url {string}
             * @param method {string}
             * @param requestData {array}
             * @param callback {function}
             */
            attachDialogCallEventOnSelector(selector, url, method, requestData, callback) {

                let element = $(selector);
                let _this   = this;

                $(element).on('click', function(){
                   _this.buildDialogBody(url, method, requestData, callback);
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

                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: method,
                    url: url,
                    data: requestData
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callDialog(data['template'], callback);
                    } else if(undefined !== data['errorMessage']) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

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
                let _this = this;
                let getDataTransferDialogTemplate = dialogs.ui.methods.getDataTransferDialogTemplate;

                let data = {
                    'files_current_locations' : filesCurrentPaths,
                    'moduleName'              : moduleName
                };

                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: getDataTransferDialogTemplate,
                    data: data
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callDataTransferDialog(data['template'], callback);
                    } else if(undefined !== data['errorMessage']) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

                })

            },
            callDataTransferDialog: function (template, callback = null) {

                let _this  = this;

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
                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url:dialogs.ui.methods.moveMultipleFiles,
                    data: data
                }).always( (data) => {
                    ui.widgets.loader.toggleLoader();

                    let responseCode = data['response_code'];
                    let message      = data['response_message'];
                    let notifyType   = '';

                    if( responseCode === 200 ){

                        if( 'function' === typeof(callback) ){
                            callback();
                            bootbox.hideAll()
                        }

                        notifyType = 'success'
                    }else if( responseCode >200 && responseCode < 300){
                        if( 'function' === typeof(callback) ){
                            callback();
                            bootbox.hideAll()
                        }
                        notifyType = 'warning';
                    }else{
                        notifyType = 'danger';
                    }

                    // not checking if code is set because if message is then code must be also
                    if( undefined !== message ){
                        bootstrap_notifications.notify(message, notifyType);
                    }
                    
                })

            },
        },
        tagManagement: {
            buildTagManagementDialog: function (fileCurrentPath, moduleName, callback = null) {
                dialogs.ui.vars.fileCurrentPath = fileCurrentPath;
                let _this = this;
                let getDialogTemplate = dialogs.ui.methods.getTagsUpdateDialogTemplate;

                let data = {
                    'fileCurrentPath': fileCurrentPath,
                    'moduleName'     : moduleName
                };
                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: getDialogTemplate,
                    data: data
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callTagManagementDialog(data['template'], callback);
                    } else if( undefined !== data['errorMessage'] ) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

                })

            },
            callTagManagementDialog: function (template, callback = null) {

                let _this  = this;

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
                    ui.widgets.selectize.applyTagsSelectize();
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
                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "POST",
                    url: dialogs.ui.methods.updateTagsForMyImages,
                    data: data
                }).always( (data) => {
                    ui.widgets.loader.toggleLoader();
                    let responseCode = data['response_code'];
                    let message      = data['response_message'];
                    let notifyType   = '';

                    if( responseCode === 200 ){

                        if( 'function' === typeof(callback) ){
                            callback(tags);
                            bootbox.hideAll()
                        }

                        notifyType = 'success'
                    }else{
                        notifyType = 'danger';
                    }

                    // not checking if code is set because if message is then code must be also
                    if( undefined !== message ){
                        bootstrap_notifications.notify(message, notifyType);
                    }

                })


            }
        },
        notePreview: {
            buildTagManagementDialog: function (noteId, categoryId, callback = null) {
                let _this = this;
                let that  = dialogs.ui;
                let getDialogTemplate = dialogs.ui.methods.getNotePreviewDialogTemplate;

                let url = getDialogTemplate.replace(that.placeholders.categoryId, categoryId);
                    url = url.replace(that.placeholders.noteId, noteId);

                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callTagManagementDialog(data['template'], callback);
                    } else if( undefined !== data['errorMessage'] ) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

                })

            },
            callTagManagementDialog: function (template, callback = null) {

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
             * @param callback {function}
             */
            buildSystemToggleLockDialog: function (callback = null) {
                let _this = this;
                let url   = dialogs.ui.methods.systemLockResourcesDialogTemplate;

                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callSystemToggleLockDialog(data['template'], callback);
                    } else if( undefined !== data['errorMessage'] ) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

                })

            },
            /**
             * Call dialog with confirmation about setting/removing lock for entire system
             * @param template {string}
             * @param callback {function}
             */
            callSystemToggleLockDialog: function (template, callback = null) {

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
                        ui.lockedResource.ajaxToggleSystemLock(password);
                    })
                });
            },
            /**
             * Build dialog for creating first time lock password
             * @param callback {function}
             */
            buildCreateLockPasswordForSystemDialog: function (callback = null) {
                let _this = this;
                let url   = dialogs.ui.methods.createSystemLockPasswordDialogTemplate;

                ui.widgets.loader.toggleLoader();
                $.ajax({
                    method: "GET",
                    url: url
                }).always((data) => {
                    ui.widgets.loader.toggleLoader();

                    if( undefined !== data['template'] ){
                        _this.callCreateLockPasswordForSystemDialog(data['template'], callback);
                    } else if( undefined !== data['errorMessage'] ) {
                        bootstrap_notifications.notify(data['errorMessage'], 'danger');
                    }else{
                        let message = 'Something went wrong while trying to load dialog template.';
                        bootstrap_notifications.notify(message, 'danger');
                    }

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
        }

    };

}());
