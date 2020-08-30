import 'bootstrap-select';
require('bootstrap-select/dist/css/bootstrap-select.css');

import StringUtils from "../../core/utils/StringUtils";
import DomAttributes from "../../core/utils/DomAttributes";

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

    private static selectors = {
        selectpicker: ".selectpicker"
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

    /**
     * @description checks if given jquery element is a selectpicker by checking it's class
     *              it does not directly check if the element has instance of selectpicker
     *              but rather if it has any attributes that say `this element should be a selectpicker`
     *
     * @param $element
     */
    public static isSelectpicter($element: JQuery<HTMLElement>): boolean
    {
        let selectpickerClassName = DomAttributes.getClassNameFromSelector(this.selectors.selectpicker);
        return $element.hasClass(selectpickerClassName);
    }

    /**
     * @description will refresh the selectpicker instance
     * @param $element
     */
    public static refreshSelector($element: JQuery<HTMLElement>): void
    {
        let classesToAdd = $element.data(BootstrapSelect.attributes.classes);

        if( StringUtils.isEmptyString(classesToAdd) ){
            classesToAdd = BootstrapSelect.defaultConfig.classes;
        }
        //@ts-ignore
        $element.selectpicker('refresh');
        //@ts-ignore
        $element.selectpicker('setStyle', classesToAdd);
    }
}