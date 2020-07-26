import AbstractAction from "./AbstractAction";
import TinyMce from "../../../libs/tiny-mce/TinyMce";
import PrismHighlight from "../../../libs/prism/PrismHighlight";

export default class EditViaTinyMceAction extends AbstractAction {

    /**
     * @type TinyMce
     */
    private tinyMce = new TinyMce();

    /**
     * @type PrismHighlight
     */
    private prismHighlight = new PrismHighlight();

    public init()
    {
        // the order is very important as in one event we block propagation to prevent accordion closing
        this.attachEventOnButtonForEditingViaTinyMce();
        this.attachEventOnButtonToTransformTargetSelectorToTinyMceInstance();
    }

    /**
     * @description Will attach logic to element so that when pressed turns the target element into tinymce
     * @param preventFurtherEventPropagation {boolean}
     */
    private attachEventOnButtonToTransformTargetSelectorToTinyMceInstance(preventFurtherEventPropagation = true){
        let $allButtons = $('.transform-to-tiny-mce');
        let _this       = this;

        $.each($allButtons, function(index, button){
            let $button = $(button);

            $button.on('click', function(event){

                let tinyMceSelector         = $button.attr(_this.data.tinymceElementSelector);
                let tinyMceInstanceSelector = $button.attr(_this.data.tinymceElementInstanceSelector);
                let tinyMceInstance         = TinyMce.getTinyMceInstanceForSelector(tinyMceInstanceSelector);

                // prevent reinitializing and make it removable when closing edit
                if( tinyMceInstance === null ){
                    _this.tinyMce.init(tinyMceSelector);
                }else{
                    TinyMce.remove(tinyMceSelector);
                    _this.prismHighlight.highlightCode();
                }

                // used for example to suppress propagating accordion open/close
                if( preventFurtherEventPropagation ){
                    event.stopPropagation();
                }
            });
        });
    };

    /**
     * @description Attaches the logic after clicking on button for editing with tinymce
     */
    private attachEventOnButtonForEditingViaTinyMce(){
        let _this             = this;
        let $allActionButtons = $('.edit-record-with-tiny-mce');

        $.each($allActionButtons, function(index, button){
            let $button    = $(button);
            let $accordion = $button.closest(_this.classes.accordion);

            $button.off('click'); // prevent stacking - also keep in mind that might remove other events attached before
            $button.on('click', function(event){
                _this.toggleActionIconsVisibility($accordion);
            });
        });

    };

}