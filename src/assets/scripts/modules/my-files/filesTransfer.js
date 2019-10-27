/**
 * This file handles the filesTransfer logic for module "My Files"
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
                    let fileCurrentPath         = $(tr).find('[name^="file_full_path"]').val();

                    let callback = function (){
                        let parent_wrapper  = $(clickedButton).closest('tr');
                        let table_id        = $(parent_wrapper).closest('tbody').closest('table').attr('id');
                        ui.crud.removeDataTableTableRow(table_id, parent_wrapper);
                    };

                    dialogs.ui.dataTransfer.buildDataTransferDialog([fileCurrentPath], 'My Files', callback);
                });

            }

        }
    };

}());
