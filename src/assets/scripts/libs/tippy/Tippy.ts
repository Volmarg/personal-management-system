import StringUtils from "../../core/utils/StringUtils";

import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light.css';

/**
 * @description this is replacement for Bootstrap Popover (which is way to glitchy at some points)
 *              the attributes `translation` (from bootstrap to tippy) was made to keep the backward compatibility with
 *              the attributes which are already set on various elements (via html / js)
 */
export default class Tippy {

    private static popoverAttributes = {
        content   : "content",
        placement : "placement"
    }

    /**
     * Will initialize Tippy by the data attr
     */
    public static init(){
        let $allElements = $('[data-toggle-popover="true"]');

        $.each($allElements, (index, element) => {

            let $element  = $(element);
            let content   = $element.data(Tippy.popoverAttributes.content)
            let placement = $element.data(Tippy.popoverAttributes.placement)

            if(
                    !StringUtils.isEmptyString(content)
                // @ts-ignore
                &&  !element._tippy
            ){
                tippy(element, {
                    allowHTML : true,
                    theme     : 'light',
                    content   : content,
                    placement : placement
                });
            }

        })
    };

}