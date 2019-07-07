/**
 * this should NOT contain any main logic of system
 *  only some smaller function passed for example in json configs etc.
 */

var myNotesCategory = {

    /**
     * Selecting option corresponding to the category on which im at atm.
     * raw js while jquerry works withing webpack scripts only
     */
    selectCurrentCategoryOptionForQuickNoteCreator: function () {
        let quickNoteSelect = document.querySelector("select#my_notes_category");
        let selectOptions = document.querySelectorAll("select#my_notes_category option");
        let getAttrs = JSON.parse(TWIG_GET_ATTRS);

        if (undefined !== getAttrs.category_id && null !== getAttrs.category_id) {
            let categoryId = getAttrs.category_id;
            quickNoteSelect.setAttribute("value", categoryId);

            selectOptions.forEach(function (option, index) {
                if (option.getAttribute("value") == categoryId) {
                    option.setAttribute("selected", "true");
                }
            });

        }
    }
}
