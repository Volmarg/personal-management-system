import * as $ from "jquery";
import BootboxWrapper from "../../libs/bootbox/BootboxWrapper";

export default class UploadSettings {

    /**
     * @type Object
     */
    private messages = {
        settings: {
            upload_subdirectory_rename_submit       : "Do You really want to rename this folder?",
            upload_subdirectory_move_data_submit    : "Do You really want to move data between these two folders?",
            upload_subdirectory_create_submit       : "Do You want to create folder with this name?"
        }
    };

    /**
     * @type Object
     */
    private selectors = {
        id: {
            subdirectoryRenameSubmit    : "#upload_subdirectory_rename_submit",
            subdirectoryMoveDataSubmit  : "#upload_subdirectory_move_data_submit",
            createSubdirectorySubmit    : "#upload_subdirectory_create_submit"
        }
    };

    /**
     * @type Object
     */
    private elements = {
        init: () => {
            this.elements.subdirectoryRenameSubmit   = $(this.selectors.id.subdirectoryRenameSubmit);
            this.elements.subdirectoryMoveDataSubmit = $(this.selectors.id.subdirectoryMoveDataSubmit);
            this.elements.createSubdirectorySubmit   = $(this.selectors.id.createSubdirectorySubmit);
        },
        subdirectoryRenameSubmit    : <JQuery> null,
        subdirectoryMoveDataSubmit  : <JQuery> null,
        createSubdirectorySubmit    : <JQuery> null
    };

    /**
     * @description Main initialization functiono
     */
    public init()
    {
        this.elements.init();
        this.addConfirmationBoxesToForms();
    }

    /**
     * @description Add confirmation boxes to forms
     */
   private addConfirmationBoxesToForms(): void
    {
        let _this = this;
        let submitButtons = [
            this.selectors.id.createSubdirectorySubmit,
            this.selectors.id.subdirectoryMoveDataSubmit,
            this.selectors.id.subdirectoryRenameSubmit,
        ];

        $.each(submitButtons, (index, submitButton) => {

            $(submitButton).on('click', (event) => {
                let clickedButton   = $(event.target);
                let buttonId        = $(clickedButton).attr('id');
                let form            = $(clickedButton).closest('form');

                let message         = _this.messages.settings[buttonId];

                event.preventDefault();

                _this.callBootBoxWithFormSubmitionOnAccept(message, form);
            });

        });
    };

    /**
     * @description Will build bootbox on form submission
     * @param message
     * @param $form
     */
    private callBootBoxWithFormSubmitionOnAccept(message: string, $form: JQuery): void
    {
        BootboxWrapper.confirm({
            message: message,
            backdrop: true,
            callback: function (result) {
                if (result) {
                    $($form).submit();
                }
            }
        });
    }

}