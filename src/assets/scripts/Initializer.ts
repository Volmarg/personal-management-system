import SearchBar                    from "./core/search/SearchBar";
import BootstrapDatepicker          from "./libs/datepicker/BootstrapDatepicker";
import Scrollbar                    from "./libs/scrollbar/Scrollbar";
import WindowEvents                 from "./core/ui/WindowEvents";
import DocumentEvents               from "./core/ui/DocumentEvents";
import ApexChartsHandler            from "./libs/apexcharts/ApexChartsHandler";
import Selectize                    from "./libs/selectize/Selectize";
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
import UploadSettings               from "./modules/Files/UploadSettings";
import LockedResource               from "./core/locked-resource/LockedResource";
import CallableViaDataAttrsDialogs  from "./core/ui/Dialogs/CallableViaDataAttrsDialogs";
import WidgetsDialogs               from "./core/ui/Dialogs/WidgetsDialogs";
import Dialog                       from "./core/ui/Dialogs/Dialog";
import FilesTransfer                from "./modules/Files/FilesTransfer";
import MonthlyPayments              from "./modules/Payments/MonthlyPayments";
import UploadBasedModules           from "./modules/UploadBasedModules";
import Sidebars                     from "./core/sidebar/Sidebars";
import CopyToClipboardAction        from "./core/ui/Actions/CopyToClipboardAction";
import CreateAction                 from "./core/ui/Actions/CreateAction";
import EditViaTinyMceAction         from "./core/ui/Actions/EditViaTinyMceAction";
import FontawesomeAction            from "./core/ui/Actions/FontawesomeAction";
import RemoveAction                 from "./core/ui/Actions/RemoveAction";
import ToggleBoolvalAction          from "./core/ui/Actions/ToggleBoolvalAction";
import UpdateAction                 from "./core/ui/Actions/UpdateAction";
import NotesTinyMce                 from "./modules/Notes/NotesTinyMce";
import BootstrapSelect              from "./libs/bootstrap-select/BootstrapSelect";
import TodoChecklist                from "./modules/Todo/TodoChecklist";
import JsCookie                     from "./libs/js-cookie/JsCookie";
import Ajax                         from "./core/ajax/Ajax";
import Loader                       from "./libs/loader/Loader";
import DomElements                  from "./core/utils/DomElements";
import VideoJs                      from "./libs/video-js/VideoJs";
import MassActions                  from "./core/ui/Actions/MassActions";
import Tippy                        from "./libs/tippy/Tippy";
import TodoModal                    from "./modules/Todo/TodoModal";
import FineUploaderService          from "./libs/fine-uploader/FineUploaderService";
import AjaxEvents                   from "./core/ajax/AjaxEvents";
import TuiCalendarService           from "./libs/tui-calendar/TuiCalendarService";
import SmartTab                     from "./libs/smarttab/SmartTab";
import JsSettingsTooltip            from "./core/ui/JsSettingsTooltip";
import PasswordPreview              from "./core/ui/Form/PasswordPreview";
import GeneratePassword             from "./libs/generate-password/GeneratePassword";

import EditViaModalPrefilledWithEntityDataAction from "./core/ui/Actions/EditViaModalPrefilledWithEntityDataAction";
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
        let videoJs           = new VideoJs();
        let fineUploadService = new FineUploaderService();
        let tuiCalendar       = new TuiCalendarService();
        let smartTab          = new SmartTab();
        let generatePassword  = new GeneratePassword();

        // core
        let search                 = new Search();
        let uploadSettings         = new UploadSettings();
        let lockedResource         = new LockedResource();
        let callableViaAttrDialogs = new CallableViaDataAttrsDialogs();
        let widgetsDialogs         = new WidgetsDialogs();
        let dialog                 = new Dialog();
        let domElements            = new DomElements();
        let ajaxEvents             = new AjaxEvents();
        let passwordPreview        = new PasswordPreview();

        // modules
        let todoChecklist       = new TodoChecklist();
        let todoModal           = new TodoModal();
        let filesTransfer       = new FilesTransfer();
        let monthlyPayments     = new MonthlyPayments();
        let uploadBasedModules  = new UploadBasedModules();
        let notesTinyMce        = new NotesTinyMce();

        // inits
        BootstrapSelect.init();
        this.initializeActions();
        Tippy.init();
        Sidebars.init();
        Sidebars.markCurrentMenuElementAsActive();
        selectize.init();
        formsUtils.init();
        uploadSettings.init();
        datatable.init();
        loadingBar.init();
        tinymce.init();
        todoChecklist.init();
        todoModal.init();
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
        notesTinyMce.init();
        monthlyPayments.init();
        uploadBasedModules.init();
        JsColor.init();

        domElements.init();

        dialog.init();

        videoJs.init();
        fineUploadService.init();

        ajaxEvents.init();
        tuiCalendar.init();
        smartTab.init();
        passwordPreview.init();
        generatePassword.init();
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
        this.handleFirstTimeLogin();

        let windowEvents   = new WindowEvents();
        let documentEvents = new DocumentEvents();
        let jsSettings     = new JsSettingsTooltip();

        jsSettings.setThemeFromCookie();

        // new
        jsSettings.init();
        SearchBar.init();
        BootstrapDatepicker.init();
        Scrollbar.init();
        windowEvents.attachEvents();
        documentEvents.attachEvents();
    }

    /**
     * @description Initialize logic for all actions
     */
    private initializeActions(): void
    {
        let copyToClipboardAction                  = new CopyToClipboardAction();
        let createAction                           = new CreateAction();
        let editModalPrefilledWithEntityDataAction = new EditViaModalPrefilledWithEntityDataAction();
        let editViaTinyMceAction                   = new EditViaTinyMceAction();
        let fontawesomeAction                      = new FontawesomeAction();
        let removeAction                           = new RemoveAction();
        let toggleBoolvalAction                    = new ToggleBoolvalAction();
        let updateAction                           = new UpdateAction();
        let massActions                            = new MassActions();

        copyToClipboardAction.init();
        createAction.init();
        editModalPrefilledWithEntityDataAction.init();
        editViaTinyMceAction.init();
        fontawesomeAction.init();
        removeAction.init();
        toggleBoolvalAction.init();
        updateAction.init();
        massActions.init();
    }

    /**
     * @description Will handle special logic designed for the first time login only
     * @private
     */
    private handleFirstTimeLogin()
    {
        if(
                !JsCookie.isHideFirstLoginInformation()
            &&  location.pathname.indexOf('/login') == -1
        ){
            let isDemoMode = DomElements.doElementsExists($('[data-guide-mode]'));

            if( isDemoMode ){
                let callableViaDataAttrsDialogs = new CallableViaDataAttrsDialogs();
                let dialogUrl                   = Ajax.getUrlForPathName('dialog_body_first_login_information');

                callableViaDataAttrsDialogs.buildDialogBody(dialogUrl, Ajax.REQUEST_TYPE_POST,{}, ()=>{
                    let prism = new PrismHighlight();
                    prism.init();
                    Loader.hideMainLoader();
                }, true, "Ok")
            }

            JsCookie.setHideFirstLoginInformation()
        }
    }

}

/**
 * @description triggering all necessary logic - must be handled this way
 */
$(document).ready(() => {
    let initializer = new Initializer();
    initializer.fullInitialization();
});