/**
 * The entry point of whole project, this is where the entire logic is being triggered on, every single thing
 * that might eventually be needed on given page is being triggered or reinitialized
 */

import ApexChartsHandler from "./libs/apexcharts/ApexChartsHandler";
import BootstrapToggle   from "./libs/bootstrap-toggle/BootstrapToggle";
import Popover           from "./libs/popover/Popover";
import Selectize         from "./libs/selectize/Selectize";
import Loader            from "./libs/loader/Loader";
import * as test from "./index"
export default (function () {

    window.initializer = {};
    initializer = {
        reinitialize: function () {
            this.init();
            this.initStatic();
        },
        init: function () {
            let apexChartsHandler = new ApexChartsHandler();
            //let bootstrapToggle   = new BootstrapToggle();
            let selectize         = new Selectize();


            events.general.init();
            ui.crud.init();
            ui.widgets.init();
            ui.widgets.popover.init();
            selectize.init();
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
            apexChartsHandler.init();
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
                Loader.showLoader();
            });

            $(window).on('load', function(){
                Loader.hideLoader();
            });

            let denyUnloadForSelectors = ['.file-download'];

            $.each(denyUnloadForSelectors, function(index, selector) {
                let $element = $(selector);
                $element.on('click', function(){
                    setTimeout(function(){
                        Loader.hideLoader();
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