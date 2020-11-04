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
import Selectize from "../../../../libs/selectize/Selectize";
import TagManagementDialogs from "../TagManagementDialogs";
import MassActions from "../../Actions/MassActions";
import Navigation from "../../../Navigation";
import BootboxWrapper from "../../../../libs/bootbox/BootboxWrapper";
import AjaxEvents from "../../../ajax/AjaxEvents";
import LightGallery from "../../../../libs/lightgallery/LightGallery";
import ModulesStructure from "../../BackendStructure/ModulesStructure";
import BootstrapSelect from "../../../../libs/bootstrap-select/BootstrapSelect";
import DataTransferDialogs from "../DataTransferDialogs";
import Loader from "../../../../libs/loader/Loader";
import * as $ from "jquery";
import DomAttributes from "../../../utils/DomAttributes";

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
     * todo: need to be replaced in the twig and retested, this was only prepared to for replacement
     *
     * @description contains definition of logic for mass action dialog - transfer data transfer, module: Images
     */
    public static massActionDataTransferImagesModule(): DialogDataDto
    {
        let massActions = new MassActions();
        let filesPaths  = massActions.getFilesPathsForAllSelectedCheckboxes();

        let callback = () => {
            let ajaxEvents   = new AjaxEvents();
            let lightGallery = new LightGallery();

            ajaxEvents.loadModuleContentByUrl(Navigation.getCurrentUri());
            lightGallery.reinitGallery();
            BootboxWrapper.hideAll();
        };

        let ajaxData = {
            'files_current_locations': filesPaths
        };

        let dialogDataDto        = new DialogDataDto();
        dialogDataDto.callback = callback;
        dialogDataDto.ajaxData = ajaxData;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for mass action dialog - transfer data transfer, module: Video
     */
    private static massActionDataTransferVideoModule(): DialogDataDto
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
            'moduleName'             : "My Video"
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;
        dialogDataDto.ajaxData = ajaxData;

        return dialogDataDto;
    }

    /**
     * @description contains definition of logic for mass action dialog - files removal, module: Video
     */
    private static massActionFilesRemovalForVideoModule(): DialogDataDto
    {
        let massActions = new MassActions();

        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let ajaxEvents        = new AjaxEvents();
            let removedFilesPaths = massActions.getFilesPathsForAllSelectedCheckboxes();

            // todo, bind event on submit button
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
}