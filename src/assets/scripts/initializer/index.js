/*
    ### CRUD - responsible for things like clicking on trash - removes ui element
    ### clicking on + creates ui element etc.
 */

export default (function () {

    window.initializer = {};
    initializer = {
        reinitialize: function () {
            this.init();
            this.initStatic();
        },
        init: function () {
            events.general.init();
            ui.crud.init();
            ui.widgets.init();
            ui.forms.init();
            ui.upload.init();
            datatable.init();
            loading_bar.init();
            tinymce.custom.init();
            myGoals.ui.init();
            gallery.lightgallery.init();
            ui.shuffler.init();
            modules.myFiles.init();
            ui.search.init();
            apexcharts.init();
            ui.lockedResource.init();
            prismjs.init();
            dialogs.ui.general.init();
            datetimepicker.init();
        },
        /**
         * Reinitialize is being called in alot of places when content is reloaded via js some logic is not allowed to
         * reloaded, called more than once in lifecycle,
         * it's NOT allowed to call this function from anywhere else than here.
         */
        oneTimeInit: function () {
            ui.ajax.init();

            $(window).on('beforeunload', function(){
                ui.widgets.loader.showLoader();
            });

            $(window).on('load', function(){
                ui.widgets.loader.hideLoader();
            });

            let denyUnloadForSelectors = ['.file-download'];

            $.each(denyUnloadForSelectors, function(index, selector) {
                let $element = $(selector);
                $element.on('click', function(){
                    setTimeout(function(){
                        ui.widgets.loader.hideLoader();
                        }, 1000);
                })
            });

        },
        initStatic: function () {
            if ("undefined" !== typeof jscolorCustom) {
                jscolorCustom.init();
            }
        },
    };

    initializer.init();
    initializer.initStatic();
    initializer.oneTimeInit();
}());
// --