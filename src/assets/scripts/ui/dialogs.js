/**
 * This file handles calling dialogs
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
                targetSubdirectoryTypeInput : '#move_single_file_target_subdirectory_path'
            },
            classes: {
                fileTransferButton      : '.file-transfer',
                bootboxModalMainWrapper : '.modal-dialog'
            }
        },
        placeholders: {
            fileName            : "%fileName%",
            targetUploadType    : "%currentUploadType%",
        },
        messages: {
        },
        methods: {
            moveSingleFile                  : '/files/action/move-single-file',
            updateTagsForMyImages           : '/images/action/update-tags',
            getDataTransferDialogTemplate   : '/dialog/body/data-transfer',
            getTagsUpdateDialogTemplate     : '/dialog/body/tags-update'
        },
        vars: {
            fileCurrentPath : '',
            tags            : ''
        },
        dataTransfer: {
            buildDataTransferDialog: function (fileName, fileCurrentPath, moduleName, callback = null) {
                dialogs.ui.vars.fileCurrentPath = fileCurrentPath;
                let _this = this;
                let getDataTransferDialogTemplate = dialogs.ui.methods.getDataTransferDialogTemplate;

                let data = {
                    'fileCurrentPath': fileCurrentPath,
                    'moduleName'     : moduleName
                };

                $.ajax({
                    method: "POST",
                    url: getDataTransferDialogTemplate,
                    data: data
                }).always((data) => {

                    if( undefined !== data['template'] ){

                        let message = data['template'].replace(dialogs.ui.placeholders.fileName, fileName);
                        _this.callDataTransferDialog(message, callback);

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

                let fileCurrentPath             = dialogs.ui.vars.fileCurrentPath;
                let targetUploadModuleDirInput  = $(dialogs.ui.selectors.ids.targetUploadModuleDirInput).val();
                let targetSubdirectoryPath      = $(dialogs.ui.selectors.ids.targetSubdirectoryTypeInput).val();

                let data = {
                    'file_current_location'                         : fileCurrentPath,
                    'target_upload_module_dir'                      : targetUploadModuleDirInput,
                    'subdirectory_target_path_in_module_upload_dir' : targetSubdirectoryPath
                };

                $.ajax({
                    method: "POST",
                    url:dialogs.ui.methods.moveSingleFile,
                    data: data
                }).always( (data) => {
                    let responseCode = data['response_code'];
                    let message      = data['response_message'];
                    let notifyType   = '';

                    if( responseCode === 200 ){

                        if( 'function' === typeof(callback) ){
                            callback();
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

                $.ajax({
                    method: "POST",
                    url: getDialogTemplate,
                    data: data
                }).always((data) => {

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

                    _this.attachTagsUpdateOnFormSubmit(formSubmitButton, callback);
                    ui.widgets.selectize.applyTagsSelectize();
                    ui.forms.init();
                });
            },
            attachTagsUpdateOnFormSubmit: function(button, callback = null){
                let _this = this;
                $(button).on('click', (event) => {
                    event.preventDefault();
                    _this.makeAjaxCallForTagsUpdate(callback);
                });
            },
            makeAjaxCallForTagsUpdate: function(callback = null){

                let fileCurrentPath = dialogs.ui.vars.fileCurrentPath;
                let tags            = "input from dialog here";

                let data = {
                    'tags'              : tags,
                    'file_current_path' : fileCurrentPath,
                };

                $.ajax({
                    method: "POST",
                    url: dialogs.ui.methods.updateTagsForMyImages,
                    data: data
                }).always( (data) => {
                    let responseCode = data['response_code'];
                    let message      = data['response_message'];
                    let notifyType   = '';

                    if( responseCode === 200 ){

                        if( 'function' === typeof(callback) ){
                            callback();
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
        }

    };

}());
