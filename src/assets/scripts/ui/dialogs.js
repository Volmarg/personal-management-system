/**
 * This file handles only the dialog used to transfer files between modules
 */
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.dialogs === 'undefined') {
        window.dialogs = {};
    }

    dialogs.ui = {

        selectors: {
            ids: {
                targetUploadTypeInput       : '#move_single_file_target_upload_type',
                targetSubdirectoryTypeInput : '#move_single_file_target_subdirectory_name'
            },
            classes: {
                fileTransferButton      : '.file-transfer',
                bootboxModalMainWrapper : '.modal-dialog'
            }
        },
        placeholders: {
            fileName: "%fileName%",
        },
        messages: {
        },
        methods: {
            moveSingleFile      : '/files/action/move-single-file',
            getDialogTemplate   : '/dialog/body/data-transfer'
        },
        vars: {
            fileCurrentPath: ''
        },
        dataTransfer: {
            buildDataTransferDialog: function (fileName, fileCurrentPath) {
                dialogs.ui.vars.fileCurrentPath = fileCurrentPath;
                let _this                       = this;

                $.ajax({
                    method: "GET",
                    url: dialogs.ui.methods.getDialogTemplate,
                }).done((data) => {

                    if( undefined !== data['template'] ){

                        let message = data['template'].replace(dialogs.ui.placeholders.fileName, fileName);
                        _this.callDataTransferDialog(message);

                    }

                }).fail(() => {
                    let message = 'Something went wrong while trying to load dialog template.';
                    bootstrap_notifications.notify(message, 'danger');
                });

            },
            callDataTransferDialog: function (template) {

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
                    callback: function () {}
                });

                dialog.init( () => {
                    let modalMainWrapper = $(dialogs.ui.selectors.classes.bootboxModalMainWrapper);
                    let form             = $(modalMainWrapper).find('form');
                    let formSubmitButton = $(form).find("[type^='submit']");

                    _this.attachDataTransferToDialogFormSubmit(formSubmitButton);
                });
            },
            attachDataTransferToDialogFormSubmit: function (button){
                let _this = this;
                $(button).on('click', (event) => {
                    event.preventDefault();
                    _this.makeAjaxCallForDataTransfer();
                });
            },
            makeAjaxCallForDataTransfer(){

                let fileCurrentPath         = dialogs.ui.vars.fileCurrentPath;
                let targetUploadType        = $(dialogs.ui.selectors.ids.targetUploadTypeInput).val();
                let targetSubdirectoryType  = $(dialogs.ui.selectors.ids.targetSubdirectoryTypeInput).val();

                let data = {
                    'file_current_location'     : fileCurrentPath,
                    'target_upload_type'        : targetUploadType,
                    'target_subdirectory_name'  : targetSubdirectoryType
                };

                $.ajax({
                    method: "POST",
                    url:dialogs.ui.methods.moveSingleFile,
                    data: data
                }).always( (data) => {
                    let responseCode = data['response_code'];
                    let message      = data['response_message'];
                    let notifyType   = ( responseCode === 200 ? 'success' : 'danger' );

                    // not checking if code is set because if message is then code must be also
                    if( undefined !== message ){
                        bootstrap_notifications.notify(message, notifyType)
                    }
                })

            },
        }

    };

}());
