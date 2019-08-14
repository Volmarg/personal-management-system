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
            ui.crud.init();
            ui.widgets.init();
            ui.forms.init();
            ui.upload.init();
            datatable.init();
            loading_bar.init();
            tinymce.custom.init();
            myGoals.ui.init();
            gallery.lightgallery.init();
        },
        initStatic: function () {
            if ("undefined" !== typeof furcanIconPicker) {
                furcanIconPicker.init();
            }
            if ("undefined" !== typeof jscolorCustom) {
                jscolorCustom.init();
            }
        },
    };

    initializer.init();
    initializer.initStatic();
}());
// --