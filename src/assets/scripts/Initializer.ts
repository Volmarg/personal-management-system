import SearchBar                    from "./core/search/SearchBar";
import BootstrapDatepicker          from "./libs/datepicker/BootstrapDatepicker";
import Scrollbar                    from "./libs/scrollbar/Scrollbar";
import WindowEvents                 from "./core/ui/WindowEvents";
import DocumentEvents               from "./core/ui/DocumentEvents";
import ApexChartsHandler            from "./libs/apexcharts/ApexChartsHandler";
import Selectize                    from "./libs/selectize/Selectize";
import Popover                      from "./libs/popover/Popover";
import TinyMce                      from "./libs/tiny-mce/TinyMce";
import PrismHighlight               from "./libs/prism/PrismHighlight";
import FlatPicker                   from "./libs/datetimepicker/FlatPicker";
import LoadingBar                   from "./libs/loading-bar/LoadingBar";
import ShuffleWrapper               from "./libs/shuffle/ShuffleWrapper";
import BootstrapToggle              from "./libs/bootstrap-toggle/BootstrapToggle";
import LightGallery                 from "./libs/lightgallery/LightGallery";
import DataTable                    from "./libs/datatable/DataTable";
import FormsUtils                   from "./core/utils/FormsUtils";
import Accordion                    from "./libs/accordion/Accordion";
import JsColor                      from "./libs/jscolor/JsColor";
import Search                       from "./core/ui/Search";
import Upload                       from "./modules/Files/Upload";
import UploadSettings               from "./modules/Files/UploadSettings";
import LockedResource               from "./core/locked-resource/LockedResource";
import CallableViaDataAttrsDialogs  from "./core/ui/Dialogs/CallableViaDataAttrsDialogs";
import WidgetsDialogs               from "./core/ui/Dialogs/WidgetsDialogs";
import Modal                        from "./core/ui/Modal/Modal";
import GoalsChecklist               from "./modules/Goals/GoalsChecklist";
import FilesTransfer                from "./modules/Files/FilesTransfer";
import NotesTinyMce                 from "./modules/Notes/NotesTinyMce";
import MonthlyPayments              from "./modules/Payments/MonthlyPayments";
import UploadBasedModules           from "./modules/UploadBasedModules";
import FormAppendAction             from "./core/ui/Actions/FormAppendAction";
import FontawesomeAction from "./core/ui/Actions/FontawesomeAction";
import ActionsInitializer from "./core/ui/Actions/ActionsInitializer";

/**
 * @description The entry point of whole project, this is where the entire logic is being triggered on, every single thing
 *              that might eventually be needed on given page is being triggered or reinitialized
 */

export default class Initializer {

    /**
     * @description Will call initialization all all required standard system logic
     */
    public initializeLogic(): void
    {
        // libs
        let apexChartsHandler = new ApexChartsHandler();
        let selectize         = new Selectize();
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
        let jscolor           = new JsColor();

        // core
        let search                 = new Search();
        let upload                 = new Upload();
        let uploadSettings         = new UploadSettings();
        let lockedResource         = new LockedResource();
        let callableViaAttrDialogs = new CallableViaDataAttrsDialogs();
        let widgetsDialogs         = new WidgetsDialogs();
        let modal                  = new Modal();

        // modules
        let goalsChecklist      = new GoalsChecklist();
        let filesTransfer       = new FilesTransfer();
        let monthlyPayments     = new MonthlyPayments();
        let uploadBasedModules  = new UploadBasedModules();

        // actions
        let formAppendAction    = new FormAppendAction();

        // inits
        ActionsInitializer.initializeAll();
        Popover.init();
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
        //notesTinyMce.init(); // todo: check how it was called in old js
        monthlyPayments.init();
        uploadBasedModules.init();
        jscolor.init();

        modal.init();
    }

    /**
     * @description Initializes the standard system logic and static one
     */
    public reinitializeLogic(): void
    {
        this.initializeLogic();
    }

    /**
     * @description Initializes everything - should be called only upon opening/reloading page without ajax
     */
    fullInitialization(): void
    {
        this.initializeLogic();
        this.oneTimeInit();
    }

    /**
     * @description Will call initialization of logic which should be called only once - in case of reloading page without ajax etc
     *              as window events needs to be bound etc, otherwise will stack on each other
     */
    private oneTimeInit(): void
    {
        let windowEvents   = new WindowEvents();
        let documentEvents = new DocumentEvents();

        // new
        SearchBar.init();
        BootstrapDatepicker.init();
        Scrollbar.init();
        windowEvents.attachEvents();
        documentEvents.attachEvents();
    }

}

/**
 * @description triggering all necessary logic - must be handled this way
 */
$(document).ready(() => {
    let initializer = new Initializer();
    initializer.fullInitialization();
});