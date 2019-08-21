/**
 * This file handles only the dialog used to transfer files between modules
 */
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.filesTransfer === 'undefined') {
        window.filesTransfer = {};
    }

    filesTransfer.ui = {

        selectors: {
            ids: {
                fileTransferButton: '#fileTransfer'
            },
            classes: {
                fileTransferButton      : '.file-transfer',
                bootboxModalMainWrapper : '.modal-dialog'
            }
        },
        elements: {
            init: function () {
                this.fileTransferButton = $(filesTransfer.ui.selectors.classes.fileTransferButton);
            },
            fileTransferButton: ''
        },
        placeholders: {
            fileName: "%fileName%",
        },
        messages: {
        },
        methods: {
            moveSingleFile: '/files/action/move-single-file'
        },
        vars: {
            fileCurrentPath: ''
        },
        init: function(){
            this.elements.init();
            this.attachCallDialogForDataTransfer();
        },
        attachCallDialogForDataTransfer: function () {
            let _this = this;

            $(this.elements.fileTransferButton).on('click', (event) => {
                let clickedButton           = $(event.target);
                let tr                      = $(clickedButton).closest('tr');
                let fileName                = $(tr).find('.file_name').text();
                _this.vars.fileCurrentPath  = $('[name^="file_full_path"]').val();

                _this.buildDataTransferDialog(fileName);
            });

        },
        buildDataTransferDialog: function (fileName) {
            let _this   = this;
            let url     = '/dialog/body/data-transfer';

            $.ajax({
                method: "GET",
                url: url,
            }).done((data) => {

                if( undefined !== data['template'] ){

                    let message = data['template'].replace(_this.placeholders.fileName, fileName);
                    _this.callDataTransferDialog(message);

                }

            }).fail(() => {
                //todo: finish this part of error handling
                console.warn('failed');

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
                let modalMainWrapper = $(_this.selectors.classes.bootboxModalMainWrapper);
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

            let fileCurrentPath         = this.vars.fileCurrentPath;
            let targetUploadType        = $('#move_single_file_target_upload_type').val();
            let targetSubdirectoryType  = $('#move_single_file_target_subdirectory_name').val();
            let _this                   = this;

            let data = {
                'file_current_location'     : fileCurrentPath,
                'target_upload_type'        : targetUploadType,
                'target_subdirectory_name'  : targetSubdirectoryType
            };

            $.ajax({
                method: "POST",
                url:_this.methods.moveSingleFile,
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

        }

    };

}());
