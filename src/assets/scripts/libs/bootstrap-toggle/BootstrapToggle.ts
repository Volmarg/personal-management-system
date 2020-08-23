import DomAttributes    from "../../core/utils/DomAttributes";
import UpdateAction     from "../../core/ui/Actions/UpdateAction";

import 'bootstrap-toggle/css/bootstrap2-toggle.min.css';
import 'bootstrap-toggle';

export default class BootstrapToggle {

    /**
     * @type UpdateAction
     */
    private updateAction = new UpdateAction();

    /**
     * @description Initialize bootstrap toggle on elements which have given attribute/data
     */
    public init(): void
    {
        let _this                  = this;
        let allElementsToTransform = $('[data-toggle-bootstrap-toggle="true"]');

        $.each(allElementsToTransform, function(index, element){
            let classes  = $(this).attr('data-toggle-class');
            let $element = $(element);
            if( "undefined" === typeof classes){
                classes = '';
            }

            //@ts-ignore
            $element.bootstrapToggle('destroy');
            //@ts-ignore
            $element.bootstrapToggle({
                size    : "small",
                onstyle : "success",
                offstyle: "info",
                style   : classes
            });

            let toggleButton = $(element).closest('.toggle');

            $(toggleButton).on('click', () =>{
                if( DomAttributes.isChecked($element)){
                    DomAttributes.unsetChecked($element);
                }else{
                    DomAttributes.setChecked($element);
                }
            })

        });

        /**
         * This function will attach:
         *   - save event for settings (normally save works with action buttons but I want it here too just for toggle with specific class);
         */
        _this.updateAction.attachContentSaveEventOnSaveIcon();
    };

}