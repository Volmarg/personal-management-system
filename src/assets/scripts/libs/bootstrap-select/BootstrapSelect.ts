import 'bootstrap-select';
require('bootstrap-select/dist/css/bootstrap-select.css');

import StringUtils from "../../core/utils/StringUtils";

/**
 * @description handles the logic of bootstrap select
 */
export default class BootstrapSelect
{

    /**
     * @type Object
     */
    private static attributes = {
        classes: "classes"
    };

    /**
     * @type Object
     */
    private static defaultConfig = {
        classes: "bg-white"
    };

    /**
     * @description main initialization logic of bootstrap select on elements
     */
    public static init(): void
    {
        $(document).ready(() => {
            let $allSelectpickers = $('.selectpicker');

            $.each($allSelectpickers, (index, element) => {
                let $element     = $(element);
                let classesToAdd = $element.data(BootstrapSelect.attributes.classes);

                if( StringUtils.isEmptyString(classesToAdd) ){
                    classesToAdd = BootstrapSelect.defaultConfig.classes;
                }

                //@ts-ignore
                $element.selectpicker('setStyle', classesToAdd);
            })
        });
    }
}