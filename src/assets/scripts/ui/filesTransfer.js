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
                fileTransferButton: '.file-transfer'
            }
        },
        elements: {
            init: function () {
                this.fileTransferButton = $(filesTransfer.ui.selectors.classes.fileTransferButton);
            },
            fileTransferButton: ''
        },
        messages: {
          dataTransferDialogBody: ''
        },
        init: function(){
            this.elements.init();
            this.attachCallDialogForDataTransfer();
        },
        attachCallDialogForDataTransfer: function () {
            let _this = this;

            $(this.elements.fileTransferButton).on('click', () => {

                // no ajax calls once the template for modal was fetched - it's always the same by now
                if( "" !== _this.messages.dataTransferDialogBody ){

                    $(this.elements.fileTransferButton).off('click');
                    $(this.elements.fileTransferButton).on('click', () => {
                        let template = _this.messages.dataTransferDialogBody;
                        _this.callDataTransferDialog(template);
                    });

                    return;
                }

                _this.buildDataTransferDialog();

            });

        },
        buildDataTransferDialog: function () {
            let _this   = this;
            let url     = '/dialog/body/data-transfer';

            $.ajax({
                method: "GET",
                url: url,
            }).done((data) => {

                if( undefined !== data['template'] ){

                    _this.messages.dataTransferDialogBody = data['template'];
                    _this.callDataTransferDialog(data['template']);

                }

            }).fail(() => {

                console.warn('failed');

            });

        },
        callDataTransferDialog: function (template) {
            bootbox.alert({
                size: "medium",
                backdrop: true,
                closeButton: false,
                message: template,
                buttons: {
                    ok: {
                        label: 'Cancel',
                        className: 'btn-primary',
                        callback: () => {

                        }
                    },
                },
                callback: function (result) {
                    if (result) {
                        _this.callDataTransferDialog();
                    }
                }
            });
        },
        attachDataTransferToDialogSubmit: function (){

        },
        makeAjaxCallForDataTransfer(){

        }

    };

}());
