import AbstractAction       from "./AbstractAction";
import FontAwesomePicker    from "../../../libs/fontawesome-picker/FontAwesomePicker";

export default class FontawesomeAction extends AbstractAction {

    /**
     * @type FontAwesomePicker
     */
    private fontAwesomePicker = new FontAwesomePicker();

    /**
     * Main initialization logic
     */
    public init(): void
    {
        this.attachFontawesomePickEventForFontawesomeAction();
    }

    /**
     * @description will attach calling furcan fontawesome picker on certain gui elements (actions)
     */
    private attachFontawesomePickEventForFontawesomeAction(): void {
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


            this.fontAwesomePicker.init({
                searchPlaceholder: 'Search Icon',
                showAllButton: 'Show All',
                cancelButton: 'Cancel',
            });
            this.fontAwesomePicker.run('.' + _this.classes["fontawesome-picker"] + index);
        })
    };
}