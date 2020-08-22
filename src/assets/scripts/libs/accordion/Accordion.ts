import DomElements from "../../core/utils/DomElements";

export default class Accordion {

    public static selectors = {
        ids: {
            accordionId: '#accordion'
        },
        classes: {
            accordionSectionClass: '.accordin-section'
        }
    };

    public init()
    {
        this.applyAccordion();
        $(document).ready(() => {
            this.fixAccordionsForDisplayFlex();
        });
    }

    /**
     * Will apply accordion on given elements
     */
    public applyAccordion(): void
    {
        //@ts-ignore
        $(Accordion.selectors.ids.accordionId).accordion({
            header      : "h3",
            collapsible : true,
            active      : false,
            autoHeight  : false
        });
    };

    /**
     * In some cases accordion won't work if display is set to flex
     *  accordion needs to be initialized first and then css must be applied so it will work fine
     */
    public fixAccordionsForDisplayFlex(): void
    {
        let accordionSectionSelectorForMyTravels = '.MyTravelIdeas .ui-accordion-content';
        let cssFlex = {
            "display"   : "flex",
            "flex-wrap" : "wrap",
            "padding"   : "5px"
        };
        let allSelectorsToFix = [
            accordionSectionSelectorForMyTravels
        ];

        $.each(allSelectorsToFix, function (index, selector) {
            if ( DomElements.doElementsExists($(selector)) ) {
                $(selector)
                    .css(cssFlex)
                    //@ts-ignore
                    .collapse('hide')
                    .hide();
            }
        });
    };
}