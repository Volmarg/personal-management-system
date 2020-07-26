/**
 * This class is only a temporary placeholder for executing logic which was added directly to twig
 *  this needs to be reorganized later, but also it's almost impossible to proceed with TS rewriting
 *  without dealing with this first
 */
import PrismHighlight from "../libs/prism/PrismHighlight";
import FlatPicker from "../libs/datetimepicker/FlatPicker";
import TinyMce from "../libs/tiny-mce/TinyMce";
import Accordion from "../libs/accordion/Accordion";
import Popover from "../libs/popover/Popover";
import DataTable from "../libs/datatable/DataTable";
import ActionsInitializer from "../core/ui/Actions/ActionsInitializer";
import Initializer from "../Initializer";

export default class TemporaryTwigImbuedLogicExecutionForDataCallDialogCallback_DataAttribute {

    public static templatesModulesMyIssuesComponentsMyIssueCardAddRecordsTwig()
    {
        return () => {
            let prism          = new PrismHighlight();
            let dateTimePicker = new FlatPicker();
            let tinymce        = new TinyMce();
            let accordion      = new Accordion();
            let datatable      = new DataTable();

            datatable.init();
            ActionsInitializer.initializeAll();
            accordion.applyAccordion();
            Popover.init();
            ActionsInitializer.initializeEditViaTinyMceAction();
            prism.init();
            dateTimePicker.init();
            tinymce.init();
        }
    }

    public static templatesModulesMyIssuesComponentsMyIssueCardPreviewAndEditTwig()
    {
        return () => {
            let prism          = new PrismHighlight();
            let dateTimePicker = new FlatPicker();
            let accordion      = new Accordion();
            let datatable      = new DataTable();

            datatable.init();
            ActionsInitializer.initializeAll();
            accordion.applyAccordion();
            Popover.init();
            ActionsInitializer.initializeEditViaTinyMceAction();
            prism.init();
            dateTimePicker.init();
        }
    }

    public static createNewIssue()
    {
        return () => {
            ActionsInitializer.initializeCreateAction();
        }
    }
}