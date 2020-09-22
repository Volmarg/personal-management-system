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
    public static addTodo()
    {
        let callback = (dialogWrapper?: JQuery<HTMLElement>) => {
            let todoChecklist = new TodoChecklist();
            let createAction  = new CreateAction();

            createAction.init();
            todoChecklist.init();
        };

        let dialogDataDto = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

}