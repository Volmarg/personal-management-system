import AbstractAction from "./AbstractAction";

export default class FontawesomeAction extends AbstractAction {

    public init()
    {
        this.attachFontawesomePickEventForFontawesomeAction();
    }

    /**
     * @description will attach calling furcan fontawesome picker on certain gui elements (actions)
     */
    private attachFontawesomePickEventForFontawesomeAction() {
        let _this = this;

        $('.' + this.classes["fontawesome-picker-input"]).each((index, input) => {
            $(input).removeClass(this.classes["fontawesome-picker-input"]);
            $(input).addClass(this.classes["fontawesome-picker-input"] + index);
        });

        $('.' + this.classes["fontawesome-picker-preview"]).each((index, input) => {
            $(input).removeClass(this.classes["fontawesome-picker-preview"]);
            $(input).addClass(this.classes["fontawesome-picker-preview"] + index);
        });

        $('.action-fontawesome').each((index, icon) => {

            $(icon).addClass('fontawesome-picker' + index);
            $(icon).attr('data-iconpicker-preview', '.' + _this.classes["fontawesome-picker-preview"] + index);
            $(icon).attr('data-iconpicker-input', '.' + _this.classes["fontawesome-picker-input"] + index);

            IconPicker.Init({
                jsonUrl: '/assets_/static-libs/furcan-iconpicker/1.5/iconpicker-1.5.0.json',
                searchPlaceholder: 'Search Icon',
                showAllButton: 'Show All',
                cancelButton: 'Cancel',
            });
            IconPicker.Run('.' + _this.classes["fontawesome-picker"] + index);
        })
    };
}