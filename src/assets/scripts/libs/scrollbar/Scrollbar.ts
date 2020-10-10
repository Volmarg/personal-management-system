import * as $           from 'jquery';
import PerfectScrollbar from 'perfect-scrollbar';
import DomElements      from "../../core/utils/DomElements";
import ConsoleLogger    from "../../core/ConsoleLogger";

export default class Scrollbar{

    /**
     * @type Object
     * @private
     */
    private static classes = {
        scrollableElement: '.scrollable'
    }

    /**
     * @type Object
     * @private
     */
    private static dataAttributesNames = {
        perfectScrollbarInstance: "perfectScrollbarInstance"
    }

    /**
     * @description will initialize main logic of Scrollbar
     */
    public static init(): void
    {
        let elements = $(Scrollbar.classes.scrollableElement);

        if (DomElements.doElementsExists(elements)) {
            elements.each((index, el) => {
                let perfectScrollbar = new PerfectScrollbar(el);
                let $element         = $(el);
                $element.data(Scrollbar.dataAttributesNames.perfectScrollbarInstance, perfectScrollbar);
            });
        }

        Scrollbar.recalculateOnMenuElementClick();
    }

    /**
     * Will trigger scrollbar when certain elements are clicked
     * This is required in case where there is a menu with clickable elements like `navbar item`
     * in such case the scrollbar needs to be recalculated
     *
     * @private
     */
    private static recalculateOnMenuElementClick(): void
    {
        let $allTriggersElements = $('.nav-item');

        $.each($allTriggersElements, (index, element) => {
            let $element = $(element);

            $element.on('click', () => {
                let $scrollableParent = $element.closest(Scrollbar.classes.scrollableElement);
                if( 0 !== $scrollableParent.length ){
                    let $perfectScrollbarInstance = $scrollableParent.data(Scrollbar.dataAttributesNames.perfectScrollbarInstance);

                    if( ! ($perfectScrollbarInstance instanceof PerfectScrollbar) ){
                        ConsoleLogger.error("This element should have a perfectScrollbar instance in data attribute, yet it has none", [{
                            "element": $perfectScrollbarInstance,
                        }]);
                        return;
                    }

                    $perfectScrollbarInstance.update();
                }
            })
        });
    }

}
