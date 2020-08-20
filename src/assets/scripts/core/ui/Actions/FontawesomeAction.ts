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

        let $inputs   = $('.' + this.classes["fontawesome-picker-input"]);
        let $previews = $('.' + this.classes["fontawesome-picker-preview"]);
        let $actions  = $('.action-fontawesome');

        let $fontawesomePickerFormTypeBlocks = $('.fontawesome-picker-form-type-block');
        let $formTypeBlocksButtons           = $fontawesomePickerFormTypeBlocks.find('[data-iconpicker="true"]');

        let $allActionsElements = $.merge($actions, $formTypeBlocksButtons);

        $inputs.each((index, input) => {
            $(input).removeClass(this.classes["fontawesome-picker-input"]);
            $(input).addClass(this.classes["fontawesome-picker-input"] + index);
        });

        $previews.each((index, input) => {
            $(input).removeClass(this.classes["fontawesome-picker-preview"]);
            $(input).addClass(this.classes["fontawesome-picker-preview"] + index);
        });

        //@ts-ignore
        $allActionsElements.each((index, icon) => {

            let skipRewriteDataAttributesForParentClasses = [
                'fontawesome-picker-form-type-block',
            ];

            $(icon).addClass('fontawesome-picker' + index);

            let skipDataAttributeRewrite = false;
            $.each(skipRewriteDataAttributesForParentClasses, (index, className) => {
                if( $(icon).parent().hasClass(className) ){
                    skipDataAttributeRewrite = true;
                }
            });

            if( !skipDataAttributeRewrite ){
                $(icon).attr('data-iconpicker-preview', '.' + _this.classes["fontawesome-picker-preview"] + index);
                $(icon).attr('data-iconpicker-input', '.' + _this.classes["fontawesome-picker-input"] + index);
            }

            this.fontAwesomePicker.init({
                searchPlaceholder: 'Search Icon',
                showAllButton: 'Show All',
                cancelButton: 'Cancel',
            });
            this.fontAwesomePicker.run('.' + _this.classes["fontawesome-picker"] + index);
        })
    };
}