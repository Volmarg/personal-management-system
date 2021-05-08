import * as $ from "jquery";
import DomElements from "../utils/DomElements";

export default class Sidebars {

    private static hrefPatternAttribute : string = 'data-href-pattern';

    private static readonly DATA_ATTRIBUTE_MENU_NODE_NAME = "data-menu-node-name";

    /**
     * @description this logic was delivered with the GUI meaning this is somehow responsible for handling menu
     */
    public static init(): void
    {
        // Sidebar links
        $('.sidebar .sidebar-menu li a').on('click', function () {
            const $this = $(this);

            if ($this.parent().hasClass('open')) {
                $this
                    .parent()
                    .children('.dropdown-menu')
                    .slideUp(200, () => {
                        $this.parent().removeClass('open');
                    });
            } else {
                $this
                    .parent()
                    .parent()
                    .children('li.open')
                    .children('.dropdown-menu')
                    .slideUp(200);

                $this
                    .parent()
                    .parent()
                    .children('li.open')
                    .children('a')
                    .removeClass('open');

                $this
                    .parent()
                    .parent()
                    .children('li.open')
                    .removeClass('open');

                $this
                    .parent()
                    .children('.dropdown-menu')
                    .slideDown(200, () => {
                        $this.parent().addClass('open');
                    });
            }
        });

        // Sidebar Activity Class
        const sidebarLinks = $('.sidebar').find('.sidebar-link');

        sidebarLinks
            .each((index, el) => {
                $(el).removeClass('active');
            })
            .filter(function () {
                const href = $(this).attr('href');
                const pattern = href[0] === '/' ? href.substr(1) : href;
                return pattern === (window.location.pathname).substr(1);
            })
            .addClass('active');

        // ٍSidebar Toggle
        $('.sidebar-toggle').off('click');
        $('.sidebar-toggle').on('click', e => {
            $('.app').toggleClass('is-collapsed');
            e.preventDefault();
        });

        /**
         * Wait untill sidebar fully toggled (animated in/out)
         * then trigger window resize event in order to recalculate
         * masonry layout widths and gutters.
         */
        $('#sidebar-toggle').click(e => {
            e.preventDefault();
            setTimeout(() => {
                //@ts-ignore
                window.dispatchEvent(window.EVENT);
            }, 300);
        });
    }

    /**
     * @description Will mark current element in menu as active one - based on current url
     */
    public static markCurrentMenuElementAsActive(): void
    {
        let currUrl       = window.location.pathname;
        let currMenuLink  = $('.sidebar-link[href="' + currUrl + '"');

        let currActiveMenuLink = $('.sidebar-menu li.nav-item a.active');

        if( !DomElements.doElementsExists(currMenuLink) ){
            let $allHrefPatternsElements = $("[" + Sidebars.hrefPatternAttribute + "]");
            let hasMatch                 = false;
            $.each($allHrefPatternsElements, (index, patternElement) => {
                let $patternElement   = $(patternElement);
                let regexpHrefPattern = new RegExp($patternElement.attr(Sidebars.hrefPatternAttribute));

                if( currUrl.match(regexpHrefPattern) ){
                    hasMatch     = true;
                    currMenuLink = $patternElement;
                    return;
                }
            })

            if(!hasMatch){
                return;
            }
        }

        // first find curr active and deactivate it
        $(currActiveMenuLink).removeClass('active');

        // set current active
        $(currMenuLink).addClass('active');
    }

    /**
     * @description will hide menu element for menu node module name
     */
    public static hideMenuElementForMenuNodeModuleName(menuNodeModuleName: string): void
    {
        let $menuElement = Sidebars.getMenuElementForMenuNodeModuleName(menuNodeModuleName);
        $menuElement.addClass("d-none");
    }

    /**
     * @description will hide menu element for menu node module name
     */
    public static showMenuElementForMenuNodeModuleName(menuNodeModuleName: string): void
    {
        let $menuElement = Sidebars.getMenuElementForMenuNodeModuleName(menuNodeModuleName);
        $menuElement.removeClass("d-none");
    }

    /**
     * @description will return menu element for menu node module name
     */
    private static getMenuElementForMenuNodeModuleName(menuNodeModuleName: string): JQuery<HTMLElement>
    {
        return $(`[${this.DATA_ATTRIBUTE_MENU_NODE_NAME}="${menuNodeModuleName.trim()}"]`);
    }

}