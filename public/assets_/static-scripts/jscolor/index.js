window.jscolorCustom = {};

jscolorCustom = {
    init: function () {
        this.initForMyNotesCategories();
    },
    initForMyNotesCategories: function () {

        let all_pickers = document.querySelectorAll('.color-picker');

        if (all_pickers.length !== 0) {

            all_pickers.forEach((element, index) => {

                if( !element.hasPicker ){
                    let color   = element.getAttribute('data-color');
                    new jscolor(element, {'value': color});
                    element.hasPicker = true;
                }

            });

        }
    }
};