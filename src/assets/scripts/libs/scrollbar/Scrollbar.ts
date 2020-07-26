import * as $           from 'jquery';
import PerfectScrollbar from 'perfect-scrollbar';
import DomElements      from "../../core/utils/DomElements";

export default class Scrollbar{

    public static init()
    {
        let elements = $('.scrollable');

        if (DomElements.doElementsExists(elements)) {
            elements.each((index, el) => {
                new PerfectScrollbar(el);
            });
        }
    }

}
