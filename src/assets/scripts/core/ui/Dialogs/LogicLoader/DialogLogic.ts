import DialogDataDto        from "../../../../DTO/DialogDataDto";
import PrismHighlight       from "../../../../libs/prism/PrismHighlight";
import FlatPicker           from "../../../../libs/datetimepicker/FlatPicker";
import TinyMce              from "../../../../libs/tiny-mce/TinyMce";
import Accordion            from "../../../../libs/accordion/Accordion";
import DataTable            from "../../../../libs/datatable/DataTable";
import Popover              from "../../../../libs/popover/Popover";
import EditViaTinyMceAction from "../../Actions/EditViaTinyMceAction";

/**
 * @description This class contains definitions of logic for given dialogs
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
            let prism          = new PrismHighlight();
            let dateTimePicker = new FlatPicker();
            let tinymce        = new TinyMce();
            let accordion      = new Accordion();
            let datatable      = new DataTable();
            let flatpicker     = new FlatPicker();

            datatable.init();
            accordion.applyAccordion();
            Popover.init();
            prism.init();
            dateTimePicker.init();
            tinymce.init();
            flatpicker.init();
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

            datatable.init();
            accordion.applyAccordion();
            Popover.init();
            editViaTinyMceAction.init();
            prism.init();
            dateTimePicker.init();
            flatpicker.init();
        };

        let dialogDataDto      = new DialogDataDto();
        dialogDataDto.callback = callback;

        return dialogDataDto;
    }

}