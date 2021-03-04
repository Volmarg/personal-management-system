import {int} from "flatpickr/dist/utils";

export default class Loader {

    private static selectors = {
        ids: {
            loader    : '#loader',
            subLoader : '#subloader'
        }
    };

    /**
     * @description Will show or hide page loader/spinner
     */
    public static toggleMainLoader() {
        let loader = $(Loader.selectors.ids.loader);

        if (loader.hasClass('fadeOut')) {
            Loader.showMainLoader();
        } else {
            Loader.hideMainLoader();
        }
    };

    /**
     * @description Will show page loader/spinner
     * Todo: add timeout support later on, and return that timeout eventually so that it can be cancelled
     *
     * @param soft
     * @param timeout
     */
    public static showMainLoader(soft = true, timeout: number = null) {
        let loader = $(Loader.selectors.ids.loader);
        Loader.showLoading(loader, soft, timeout);
    };

    /**
     * @description Will hide page loader/spinner
     *              Timeout is needed as in some cases when the project is being run in better environment the hide is kinda being executed to fast
     */
    public static hideMainLoader() {
        let loader = $(Loader.selectors.ids.loader);
        Loader.hideLoading(loader)
    }

    /**
     * @description Will show page loader/spinner
     * Todo: add timeout support later on, and return that timeout eventually so that it can be cancelled
     *
     * @param soft
     * @param timeout
     */
    public static showSubLoader(soft = true, timeout: number = null) {
        let loader = $(Loader.selectors.ids.subLoader);
        Loader.showLoading(loader, soft, timeout);
    };

    /**
     * @description Will hide page loader/spinner
     *              Timeout is needed as in some cases when the project is being run in better environment the hide is kinda being executed to fast
     */
    public static hideSubLoader() {
        let loader = $(Loader.selectors.ids.subLoader);
        Loader.hideLoading(loader)
    }

    /**
     * Handle showing any kind of loader
     *
     * @param loader
     * @param soft
     * @param timeout
     */
    private static showLoading(loader: JQuery<HTMLElement>, soft = true, timeout: number = null): void
    {
        if (loader.hasClass('fadeOut')) {
            if (soft) {
                loader.attr('style', 'background: rgba(255,255,255,0.5) !important');
            }
            loader.removeClass('fadeOut');
        }
    }

    /**
     * Handle hiding any kind of loader
     *
     * @param loader
     */
    private static hideLoading(loader: JQuery<HTMLElement>): void
    {
        setTimeout(function () {
            loader.removeAttr('style');
            loader.addClass('fadeOut');
        }, 200)
    }
}

