/**
 * This file handles the filesTransfer logic for module "My Files"
 */
export default class FilesTransfer {

    private static selectors = {
        classes: {
            fileTransferButton: '.file-transfer'
        }
    };

    private elements = {
        init: function () {
            this.fileTransferButton = $(FilesTransfer.selectors.classes.fileTransferButton);
        },
        fileTransferButton: '',
    };

    /**
     * Main initialization logic
     */
    public init(): void
    {
        this.elements.init();
        this.attachCallDialogForDataTransfer();
    };

    /**
     * Calls the dialog for data transfer
     */
    private attachCallDialogForDataTransfer(): void
    {
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