window.furcanIconPicker = {};

furcanIconPicker = {
    identifiers: {
        'iconpicker-button': 'input[data-iconpicker="true"]'
    },
    init: function () {
        this.initForMyNotesCategories();
    },
    initForMyNotesCategories: function () {
        let icon_picker = document.querySelector(this.identifiers["iconpicker-button"]);
        if (icon_picker !== undefined && icon_picker !== false && icon_picker !== null) {
            IconPicker.Init({
                jsonUrl: '/assets_/static-libs/furcan-iconpicker/1.5/iconpicker-1.5.0.json',
                searchPlaceholder: 'Search Icon',
                showAllButton: 'Show All',
                cancelButton: 'Cancel',
            });
            IconPicker.Run(this.identifiers["iconpicker-button"]);
        }
    }
};