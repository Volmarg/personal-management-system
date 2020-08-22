export default class Loader {

    private static selectors = {
        ids: {
            loader: '#loader'
        }
    };

    /**
     * @description Ill show or hide page loader/spinner
     */
    public static toggleLoader() {
        let loader = $(Loader.selectors.ids.loader);

        if (loader.hasClass('fadeOut')) {
            Loader.showLoader();
        } else {
            Loader.hideLoader();
        }
    };

    /**
     * @description Will show page loader/spinner
     *
     * @param soft
     */
    public static showLoader(soft = true) {
        let loader = $(Loader.selectors.ids.loader);

        if (loader.hasClass('fadeOut')) {
            if (soft) {
                loader.attr('style', 'background: rgba(255,255,255,0.5) !important');
            }
            loader.removeClass('fadeOut');
        }

    };

    /**
     * @description Will hide page loader/spinner
     *              Timeout is needed as in some cases when the project is being run in better environment the hide is kinda being executed to fast
     */
    public static hideLoader() {
        setTimeout(function () {
            let loader = $(Loader.selectors.ids.loader);

            loader.removeAttr('style');
            loader.addClass('fadeOut');
        }, 200)
    }
}

