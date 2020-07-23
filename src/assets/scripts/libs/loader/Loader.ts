export default class Loader {

    /**
     * Ill show or hide page loader/spinner
     */
    public static toggleLoader() {
        let loader = $(ui.widgets.elements.loader);

        if (loader.hasClass('fadeOut')) {
            Loader.showLoader();
        } else {
            Loader.hideLoader();
        }
    };

    /**
     * Will show page loader/spinner
     * @param soft
     */
    public static showLoader(soft = true) {
        let loader = $(ui.widgets.elements.loader);

        if (loader.hasClass('fadeOut')) {
            if (soft) {
                loader.attr('style', 'background: rgba(255,255,255,0.5) !important');
            }
            loader.removeClass('fadeOut');
        }

    };

    /**
     * Will hide page loader/spinner
     * Timeout is needed as in some cases when the project is being run in better environment the hide is kinda being executed to fast
     */
    public static hideLoader() {
        setTimeout(function () {
            let loader = $(ui.widgets.elements.loader);

            loader.removeAttr('style');
            loader.addClass('fadeOut');
        }, 200)
    }
}

