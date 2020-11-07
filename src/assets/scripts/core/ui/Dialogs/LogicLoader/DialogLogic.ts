import DialogDataDto        from "../../../../DTO/DialogDataDto";
import PrismHighlight       from "../../../../libs/prism/PrismHighlight";
import FlatPicker           from "../../../../libs/datetimepicker/FlatPicker";
import TinyMce              from "../../../../libs/tiny-mce/TinyMce";
import Accordion            from "../../../../libs/accordion/Accordion";
import DataTable            from "../../../../libs/datatable/DataTable";
import Popover              from "../../../../libs/popover/Popover";
import EditViaTinyMceAction from "../../Actions/EditViaTinyMceAction";
import FontawesomeAction    from "../../Actions/FontawesomeAction";
import UpdateAction         from "../../Actions/UpdateAction";
import CreateAction         from "../../Actions/CreateAction";
import RemoveAction         from "../../Actions/RemoveAction";
import TodoChecklist        from "../../../../modules/Todo/TodoChecklist";
import Selectize            from "../../../../libs/selectize/Selectize";
import MassActions          from "../../Actions/MassActions";
import Navigation           from "../../../Navigation";
import BootboxWrapper       from "../../../../libs/bootbox/BootboxWrapper";
import AjaxEvents           from "../../../ajax/AjaxEvents";
import LightGallery         from "../../../../libs/lightgallery/LightGallery";
import BootstrapSelect      from "../../../../libs/bootstrap-select/BootstrapSelect";
import DataTransferDialogs  from "../DataTransferDialogs";

import * as $ from "jquery";

/**
 * @description This class contains definitions of logic for given dialogs loaded/created via html data attrs.
 *
 *              Dialog name in Twig must be the same as function here:
 *              @see data-dialog-name="myIssueCardAddRecords"
 */
export default class DialogLogic {

    /**
     * @description contains definition of logic for:
     * @see         templates/modules/my-issues/components/my-issue-card.twig
     */
    public static myIssueCardAddRecords(): DialogDataDto
    {

        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let prism              = new PrismHighlight();
            let dateTimePicker     = new FlatPicker();
            let tinymce            = new TinyMce();
            let accordion          = new Accordion();
            let datatable          = new DataTable();
            let flatpicker         = new FlatPicker();
            let fontawesomeAction  = new FontawesomeAction();
            let createAction       = new CreateAction();

            datatable.init();
            accordion.applyAccordion();
            Popover.init();
            prism.init();
            dateTimePicker.init();
            tinymce.init();
            fontawesomeAction.init();
            flatpicker.init();
            createAction.init();
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for:
     * @see         templates/modules/my-issues/components/my-issue-card.twig
     */
    public static myIssueCardPreviewAndEdit()
    {

        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let prism                = new PrismHighlight();
            let dateTimePicker       = new FlatPicker();
            let accordion            = new Accordion();
            let datatable            = new DataTable();
            let flatpicker           = new FlatPicker();
            let editViaTinyMceAction = new EditViaTinyMceAction();
            let updateAction         = new UpdateAction();
            let removeAction         = new RemoveAction();
            let fontawesomeAction    = new FontawesomeAction();

            datatable.init();
            accordion.applyAccordion();
            Popover.init();
            editViaTinyMceAction.init();
            prism.init();
            dateTimePicker.init();
            flatpicker.init();
            updateAction.init();
            removeAction.init();
            fontawesomeAction.init();
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for add/modify todo
     */
    public static addOrModifyTodo()
    {
        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let todoChecklist = new TodoChecklist();
            let createAction  = new CreateAction();
            let removeAction  = new RemoveAction();
            let updateAction  = new UpdateAction();

            createAction.init();
            todoChecklist.init();
            removeAction.init();
            updateAction.init();
            Popover.init();
        };

        let dialogDataDto = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

    /**
     * @description contains definition of updating tags via dialog
     */
    public static tagsUpdate(): DialogDataDto
    {
        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let updateAction = new UpdateAction();
            let selectize    = new Selectize();

            selectize.applyTagsSelectize();
            updateAction.init();
        };

        let dialogDataDto        = new DialogDataDto();
        dialogDataDto.callback   = callback;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for mass action dialog - transfer data transfer, module: Images
     */
    public static massActionDataTransferImagesModule(): DialogDataDto
    {
        return DialogLogic.massActionDataTransferForModule('My Images');
    }

    /**
     * @description contains definition of logic for mass action dialog - transfer data transfer, module: Video
     */
    public static massActionDataTransferVideoModule(): DialogDataDto
    {
        return DialogLogic.massActionDataTransferForModule('My Video');
    }

    /**
     * @description contains definition of logic for mass action dialog - files removal, module: images
     */
    public static massActionFilesRemoval(): DialogDataDto
    {
        let massActions = new MassActions();

        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let ajaxEvents        = new AjaxEvents();
            let removedFilesPaths = massActions.getFilesPathsForAllSelectedCheckboxes();

            $('[type^="submit"]').on('click', (event) => {
                let callback = function(){
                    ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
                };
                event.preventDefault();
                ajaxEvents.callAjaxFileRemovalForFilePath(removedFilesPaths, callback);
            });
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for mass action dialog for given module
     * @param moduleName
     */
    private static massActionDataTransferForModule(moduleName: string): DialogDataDto
    {
        let massActions = new MassActions();
        let filesPaths  = massActions.getFilesPathsForAllSelectedCheckboxes();

        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let dataTransferDialogs = new DataTransferDialogs();

            let submitButton        = dialogWrapper.find('[type^="submit"]');
            let jsonFilesPaths      = dialogWrapper.find("[data-transferred-files-json]").attr('data-transferred-files-json');
            let filesPathsArray     = JSON.parse(jsonFilesPaths);

            dataTransferDialogs.attachDataTransferToDialogFormSubmit(submitButton, filesPathsArray, () => {
                let ajaxEvents = new AjaxEvents();
                ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
            });

            BootstrapSelect.init();
        };

        let ajaxData = {
            'files_current_locations': filesPaths,
            'moduleName'             : moduleName
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;
        dialogDataDto.ajaxData = ajaxData;

        return dialogDataDto;
    }
}