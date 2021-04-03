import JquerySmartTab from "./src/JquerySmartTab";
import DomElements from "../../core/utils/DomElements";

/**
 * Handles switching the content via panels
 * @see `templates/modules/my-schedules/components/calendar-settings-modal.html.twig` to check how it's being used
 * <div data-smart-tab="true">
 *    <ul class="nav">
 *       <li><a class="nav-link" href="#tab-1" id="tab-nav-1"></a></li>
 *       <li><a class="nav-link" href="#tab-2" id="tab-nav-2"></a></li>
 *    </ul>
 *
 *    <div class="tab-content">
 *       <div id="tab-1" class="tab-pane" role="tabpanel"></div>
 *       <div id="tab-2" class="tab-pane" role="tabpanel"></div>
 *    </div>
 * </div>
 *
 * @link https://github.com/techlab/jquery-smarttab
 * @link http://techlaboratory.net/jquery-smarttab#documentation
 */
export default class SmartTab
{

    readonly DATA_ATTRIBUTE_SMART_TAB = "data-smart-tab";
    readonly TAB_PANE_SELECTOR        = '.tab-pane';
    readonly DEFAULT_ACTIVE_TAB       = 0; // first one

    /**
     * @description will initialize the logic
     */
    public init()
    {
        let $allElementsToHandle = $(`[${this.DATA_ATTRIBUTE_SMART_TAB}]`);
        $allElementsToHandle.each( (index, htmlElement) => {
            this.initializeForHtmlElement(htmlElement);
        })
    }

    /**
     * @description will initialize the smart tab for given dom element
     *
     * @param htmlElement
     * @private
     */
    private initializeForHtmlElement(htmlElement: HTMLElement): void
    {
        let jquerySmartTab = new JquerySmartTab();
        let $element = $(htmlElement);

        // no panes are present - do not initialize as smart tab will throw exceptions otherwise
        if( !DomElements.doElementsExists($element.find(this.TAB_PANE_SELECTOR)) ){
            return;
        }

        jquerySmartTab.initForElementAndOptions($element, {
            selected          : this.DEFAULT_ACTIVE_TAB, // Initial selected tab, 0 = first tab (self: note - not working, first tab is always active but that's fine),
            theme             : 'github',                // theme for the tab, related css need to include for other than default theme
            orientation       : 'horizontal',            // Nav menu orientation. horizontal/vertical
            justified         : true,                    // Nav menu justification. true/false
            autoAdjustHeight  : true,                    // Automatically adjust content height
            backButtonSupport : true,                    // Enable the back button support
            enableURLhash     : true,                    // Enable selection of the tab based on url hash
            transition: {
                animation : 'none', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
                speed     : '400',  // Transion animation speed
                easing    :''       // Transition animation easing. Not supported without a jQuery easing plugin
            },
            autoProgress: {          // Auto navigate tabs on interval
                enabled     : false, // Enable/Disable Auto navigation
                interval    : 3500,  // Auto navigate Interval (used only if "autoProgress" is set to true)
                stopOnFocus : true,  // Stop auto navigation on focus and resume on outfocus
            },
            keyboardSettings: {
                keyNavigation : false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
            }
        });
    }

}