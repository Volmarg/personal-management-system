import DomAttributes from "../../core/utils/DomAttributes";

import 'bootstrap-toggle/css/bootstrap2-toggle.min.css';
import 'bootstrap-toggle';

export default class BootstrapToggle {

    /**
     * @type DomAttributes
     */
    private domAttributes = new DomAttributes();

    /**
     * Initialize bootstrap toggle on elements which have given attribute/data
     */
    public init(): void
    {
        var window = window;

        let _this                  = this;
        let allElementsToTransform = $('[data-toggle-bootstrap-toggle="true"]');

        $.each(allElementsToTransform, function(index, element){
            let classes  = $(this).attr('data-toggle-class');
            let $element = $(element);
            if( "undefined" === typeof classes){
                classes = '';
            }

            //@ts-ignore
            $element.bootstrapToggle({
                size    : "small",
                onstyle : "success",
                offstyle: "info",
                style   : classes
            });

            let toggleButton = $(element).closest('.toggle');

            $(toggleButton).on('click', () =>{
                if( _this.domAttributes.isChecked($element)){
                    _this.domAttributes.unsetChecked($element);
                }else{
                    _this.domAttributes.setChecked($element);
                }
            })

        });

        /**
         * This function will attach:
         *   - save event for settings (normally save works with action buttons but I want it here too just for toggle with specific class);
         *   - todo: check if I can compile everything without building the extensive Actions logic for TS
         */
        window.ui.crud.attachContentSaveEventOnSaveIcon();
    };

}