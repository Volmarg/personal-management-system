import 'bootstrap-select';
require('bootstrap-select/dist/css/bootstrap-select.css');

import StringUtils   from "../../core/utils/StringUtils";
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
        classes                              : "classes",
        appendClassesToDefaultClasses        : "append-classes-to-default-classes",
        appendClassesToBootstrapSelectParent : "append-classes-to-bootstrap-select-parent",
        appendClassesToBootstrapSelectButton : "append-classes-to-bootstrap-select-button"
    };

    /**
     * @type Object
     */
    private static defaultConfig = {
        classes : "bg-white bootstrap-select-capitalized-text margin-10-left-right",
    };

    private static selectors = {
        selectpicker : ".selectpicker",
        divWrapper   : ".bootstrap-select"
    };

    /**
     * @description main initialization logic of bootstrap select on elements
     */
    public static init(): void
    {
        $(document).ready(() => {
            let $allSelectpickers = $('.selectpicker');

            $.each($allSelectpickers, (index, element) => {
                let $element = $(element);
                BootstrapSelect.handleDisabledProperty($element);
                BootstrapSelect.applyClassesToElement($element);
                BootstrapSelect.afterInit($element);
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
        //@ts-ignore
        $element.selectpicker('refresh');

        BootstrapSelect.handleDisabledProperty($element);
        BootstrapSelect.applyClassesToElement($element);
        BootstrapSelect.afterInit($element);
    }

    /**
     * @description Will initialize selectpicker for element
     */
    private static afterInit($element: JQuery<HTMLElement>): void
    {
        // explicitly after init
        let $divWrapper  = $element.closest(BootstrapSelect.selectors.divWrapper);
        let $button      = $divWrapper.find('button');

        let classesToAddToDivWrapper = $element.data(BootstrapSelect.attributes.appendClassesToBootstrapSelectParent);
        let classesToAddToButton     = $element.data(BootstrapSelect.attributes.appendClassesToBootstrapSelectButton);

        // form control breaks size of other elements
        $divWrapper.removeClass('form-control');
        $divWrapper.addClass(classesToAddToDivWrapper);
        $button.addClass(classesToAddToButton);
    }

    /**
     * @description Will apply set of classes to given element, like for example:
     *              - general set of classes,
     *              - set of classes which are appended to the default classes set
     *
     * @param $element
     */
    private static applyClassesToElement($element: JQuery<HTMLElement>): void
    {
        let classesToAdd             = $element.data(BootstrapSelect.attributes.classes);
        let classesToAppendToDefault = $element.data(BootstrapSelect.attributes.appendClassesToDefaultClasses);

        if( StringUtils.isEmptyString(classesToAdd) ){

            if( StringUtils.isEmptyString(classesToAppendToDefault) ){
                classesToAppendToDefault = "";
            }

            classesToAdd = BootstrapSelect.defaultConfig.classes + " " + classesToAppendToDefault;
        }
        // @ts-ignore
        $element.selectpicker('setStyle', classesToAdd);
    }

    /**
     * @description This will set the disabled property for bootstrapselect if the select is disabled
     *              this is required due to the fact that the way bootstrapselect that is doing this
     *              is not fully correct, it does set the disabled state for `div` but this causes
     *              issues when toggling programmatically
     */
    private static handleDisabledProperty($element: JQuery<HTMLElement>): void
    {
        // the confusing part to understand here is that Select in DOM uses `attr` but bootstrapselect uses `prop`
        if ( DomAttributes.isDisabledAttribute($element) ) {
            let $bootstrapSelectButton = $element.closest('.bootstrap-select').find('button');
            $element.prop('disabled', true)
            $bootstrapSelectButton.addClass('disabled');
        }
    }
}