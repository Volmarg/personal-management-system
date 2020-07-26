/**
 * The entry point of whole project, this is where the entire logic is being triggered on, every single thing
 * that might eventually be needed on given page is being triggered or reinitialized
 */
import ApexChartsHandler from "./libs/apexcharts/ApexChartsHandler";
import BootstrapToggle   from "./libs/bootstrap-toggle/BootstrapToggle";
import Popover           from "./libs/popover/Popover";
import Selectize         from "./libs/selectize/Selectize";
import Loader            from "./libs/loader/Loader";
import GoalsChecklist    from "./modules/Goals/GoalsChecklist";
import TinyMce           from "./libs/tiny-mce/TinyMce";
import FlatPicker        from "./libs/datetimepicker/FlatPicker";
import PrismHighlight    from "./libs/prism/PrismHighlight";
import FilesTransfer     from "./modules/Files/FilesTransfer";
import Search            from "./core/ui/Search";
import Upload            from "./modules/Files/Upload";
import UploadSettings    from "./modules/Files/UploadSettings";
import LockedResource    from "./core/LockedResource";
import LoadingBar        from "./libs/loading-bar/LoadingBar";
import NotesTinyMce      from "./modules/Notes/NotesTinyMce";
import ShuffleWrapper    from "./libs/shuffle/ShuffleWrapper";
import LightGallery      from "./libs/lightgallery/LightGallery";
import DataTable         from "./libs/datatable/DataTable";
import Ajax              from "./core/ui/Ajax";
import CallableViaDataAttrsDialogs  from "./core/ui/Dialogs/CallableViaDataAttrsDialogs";
import FormsUtils                   from "./core/utils/FormsUtils";
import Accordion                    from "./libs/accordion/Accordion";
import MonthlyPayments from "./modules/Payments/MonthlyPayments";
import UploadBasedModules from "./modules/UploadBasedModules";
import WidgetsDialogs from "./core/ui/Dialogs/WidgetsDialogs";

export default (function () {

    window.initializer = {};
    initializer = {
        reinitialize: function () {
            this.init();
            this.initStatic();
        },
        init: function () {

            // libs
            let apexChartsHandler = new ApexChartsHandler();
            let selectize         = new Selectize();
            let popover           = new Popover();
            let tinymce           = new TinyMce();
            let prism             = new PrismHighlight();
            let flatpicker        = new FlatPicker();
            let loadingBar        = new LoadingBar();
            let shuffler          = new ShuffleWrapper();
            let bootstrapToogle   = new BootstrapToggle();
            let lightGallery      = new LightGallery();
            let datatable         = new DataTable();
            let formsUtils        = new FormsUtils();
            let accordion         = new Accordion();

            // core
            let search                 = new Search();
            let upload                 = new Upload();
            let uploadSettings         = new UploadSettings();
            let lockedResource         = new LockedResource();
            let callableViaAttrDialogs = new CallableViaDataAttrsDialogs();
            let widgetsDialogs         = new WidgetsDialogs();

            // modules
            let goalsChecklist      = new GoalsChecklist();
            let filesTransfer       = new FilesTransfer();
            let notesTinyMce        = new NotesTinyMce();
            let monthlyPayments     = new MonthlyPayments();
            let uploadBasedModules  = new UploadBasedModules();

            // inits
            events.general.init();
            popover.init();
            selectize.init();
            formsUtils.init();
            upload.init();
            uploadSettings.init();
            datatable.init();
            loadingBar.init();
            tinymce.init();
            goalsChecklist.init();
            lightGallery.init();
            shuffler.init();
            bootstrapToogle.init();
            accordion.init();
            filesTransfer.init();
            search.init();
            apexChartsHandler.init();
            lockedResource.init();
            prism.init();
            callableViaAttrDialogs.init();
            widgetsDialogs.init();
            flatpicker.init();
            notesTinyMce.init(); // todo: check how it was called in old js
            monthlyPayments.init();
            uploadBasedModules.init();


        },
        /**
         * Reinitialize is being called in alot of places when content is reloaded via js some logic is not allowed to
         * reloaded, called more than once in lifecycle,
         * it's NOT allowed to call this function from anywhere else than here.
         */
        oneTimeInit: function () {
            let ajax = new Ajax();

            ajax.init();

            $(window).on('beforeunload', function(){
                Loader.showLoader();
            });

            $(window).on('load', function(){
                Loader.hideLoader();
            });

            let denyUnloadForSelectors = ['.file-download'];

            $.each(denyUnloadForSelectors, function(index, selector) {
                let $element = $(selector);
                $element.on('click', function(){
                    setTimeout(function(){
                        Loader.hideLoader();
                        }, 1000);
                })
            });

        },
        initStatic: function () {
            if ("undefined" !== typeof jscolorCustom) {
                jscolorCustom.init();
            }
        },
    };
}());
// --