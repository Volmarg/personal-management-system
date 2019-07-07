window.furcanIconPicker = {};

furcanIconPicker = {
    identifiers: {
        'iconpicker-button': 'my_notes_categories_iconButton'
    },
    init: function () {
        this.initForMyNotesCategories();
    },
    initForMyNotesCategories: function () {
        let notes_category_picker = document.getElementById(this.identifiers["iconpicker-button"]);
        if (notes_category_picker !== undefined && notes_category_picker !== false && notes_category_picker !== null) {
            IconPicker.Init({
                jsonUrl: '/assets_/static-libs/furcan-iconpicker/iconpicker-1.0.0.json',
                searchPlaceholder: 'Search Icon',
                showAllButton: 'Show All',
                cancelButton: 'Cancel',
            });
            IconPicker.Run('#' + this.identifiers["iconpicker-button"]);
        }
    }
};