import SearchBar            from "./core/search/SearchBar";
import BootstrapDatepicker  from "./libs/datepicker/BootstrapDatepicker";
import Scrollbar            from "./libs/scrollbar/Scrollbar";
import WindowEvents         from "./core/ui/WindowEvents";
import DocumentEvents       from "./core/ui/DocumentEvents";
// todo: due to circular reference - this might need to be split further
export default class Initializer {

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
    }

    /**
     * Initializes everything - should be called only upon opening/reloading page without ajax
     */
    fullInitialization(): void
    {
        this.initializeLogic();
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