import SearchBar            from "./core/search/SearchBar";
import BootstrapDatepicker  from "./libs/datepicker/BootstrapDatepicker";
import Scrollbar            from "./libs/scrollbar/Scrollbar";
import WindowEvents         from "./core/ui/WindowEvents";
import DocumentEvents       from "./core/ui/DocumentEvents";

export default class Initializer {

    /**
     * Will load logic from scripts not defined in assets part but in public - from external libraries etc.
     * todo: should become part of assets at some point
     */
    public initStaticScriptsLogic(): void
    {

    }

    /**
     * Will call initialization all all required standard system logic
     */
    public initializeLogic(): void
    {

    }

    /**
     * Initializes the standard system logic and static one
     */
    public reinitializeLogic(): void
    {
        this.initializeLogic();
        this.initStaticScriptsLogic();
    }

    /**
     * Initializes everything - should be called only upon opening/reloading page without ajax
     */
    fullInitialization(): void
    {
        this.initializeLogic();
        this.initStaticScriptsLogic();
        this.oneTimeInit();
    }

    /**
     * Will call initialization of logic which should be called only once - in case of reloading page without ajax etc
     * as window events needs to be bound etc, otherwise will stack on each other
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