import * as $       from "jquery";
import Ajax         from "./Ajax";
import StringUtils  from "../utils/StringUtils";

/**
 * @default This class contains (and should remain like this) only definitions of events related to logic in
 *          @see ./Ajax.ts
 */
export default class AjaxEvents {

    /**
     * @type Ajax
     */
    private ajax = new Ajax();

    public init()
    {
        this.attachModuleContentLoadingViaAjaxOnMenuLinks();
    }

    /**
     * Attaches module content load by clicking on links in menu
     */
    public attachModuleContentLoadingViaAjaxOnMenuLinks(): void
    {
        let _this = this;
        let excludeHrefsRegexPatterns = [
            /^javascript.*/g,
            /^#.*/g
        ];

        let allElements = $('.sidebar-menu .sidebar-link, .ajax-content-load');

        $.each(allElements, (index, element) => {
            let href = $(element).attr('href');
            let skip = false;

            $.each(excludeHrefsRegexPatterns, (index, pattern) => {
                if( null !== href.match(pattern) ){
                    skip = true;
                }
            });

            if ( !skip && StringUtils.isEmptyString(href) ){
                skip = true;
            }

            if( !skip ){

                $(element).on('click', (event) => {

                    event.preventDefault();
                    _this.ajax.loadModuleContentByUrl(href);
                })

            }

        })
    }

}