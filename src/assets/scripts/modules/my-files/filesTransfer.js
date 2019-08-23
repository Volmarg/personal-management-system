/**
 * This file handles only the dialog used to transfer files between modules
 */
var bootbox = require('bootbox');

export default (function () {

    if (typeof window.modules === 'undefined') {
        window.modules = {};
    }

    modules.myFiles = {
        selectors: {
            classes: {
                fileTransferButton: '.file-transfer'
            }
        },
        elements: {
            init: function () {
                this.fileTransferButton = $(modules.myFiles.selectors.classes.fileTransferButton);
            },
            fileTransferButton: ''
        },
        init: function(){
            this.elements.init();
            this.attachCallDialogForDataTransfer();
        },
        attachCallDialogForDataTransfer: function () {
            let button  = $(this.elements.fileTransferButton);

            if( $(button).length > 0 ){

                $(this.elements.fileTransferButton).on('click', (event) => {
                    let clickedButton           = $(event.target);
                    let tr                      = $(clickedButton).closest('tr');
                    let fileName                = $(tr).find('.file_name').text();
                    let fileCurrentPath         = $('[name^="file_full_path"]').val();

                    dialogs.ui.dataTransfer.buildDataTransferDialog(fileName, fileCurrentPath, 'My Files');
                });

            }

        }
    };

}());
