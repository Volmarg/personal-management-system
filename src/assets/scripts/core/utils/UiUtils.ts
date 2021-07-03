import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";

export default class UiUtils {

    /**
     * @description Will keep open menu elements built from folders structure
     */
    public keepUploadBasedMenuOpen(){
        let openedMenuParent = $('.folder-based-menu .active').closest('li');
        while( $(openedMenuParent).hasClass('folder-based-menu-element') ){
            openedMenuParent = $(openedMenuParent).parent();
            $(openedMenuParent).addClass('open');
            $(openedMenuParent).css({
                "display": "block"
            });
        }
    }

    /**
     * @description Will show red message and redirect to given url
     *
     * @param url
     * @param message
     */
    public static redirectWithMessage (url: string, message: string): void
    {
        let bootstrapNotify = new BootstrapNotify();
        bootstrapNotify.showRedNotification(message);

        setTimeout(function () {
            globalThis.window.location.pathname = url;
        }, 3000)
    }

}