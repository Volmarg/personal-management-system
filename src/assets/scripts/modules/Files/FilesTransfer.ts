/**
 * @description This file handles the filesTransfer logic for module "My Files"
 */
import DataTransferDialogs  from "../../core/ui/Dialogs/DataTransferDialogs";
import RemoveAction         from "../../core/ui/Actions/RemoveAction";
import BootboxWrapper       from "../../libs/bootbox/BootboxWrapper";
import DomElements          from "../../core/utils/DomElements";

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
     * @description Main initialization logic
     */
    public init(): void
    {
        this.elements.init();
        this.attachCallDialogForDataTransfer();
    };

    /**
     * @type DataTransferDialogs
     */
    private dataTransferDialogs = new DataTransferDialogs();

    /**
     * @type RemoveAction
     */
    private removeAction = new RemoveAction();

    /**
     * @description Calls the dialog for data transfer
     */
    private attachCallDialogForDataTransfer(): void
    {
        let button  = $(this.elements.fileTransferButton);
        let _this   = this;

        if( DomElements.doElementsExists($(button)) ){

            $(this.elements.fileTransferButton).on('click', (event) => {
                let clickedButton           = $(event.target);
                let tr                      = $(clickedButton).closest('tr');
                let fileCurrentPath         = $(tr).find('[name^="file_full_path"]').val();

                let callback = function (){
                    let parent_wrapper  = $(clickedButton).closest('tr');
                    let table_id        = $(parent_wrapper).closest('tbody').closest('table').attr('id');
                    _this.removeAction.removeDataTableTableRow(table_id, parent_wrapper);
                    BootboxWrapper.hideAll();;
                };

                _this.dataTransferDialogs.buildDataTransferDialog([fileCurrentPath], 'My Files', callback);
            });

        }

    }
};