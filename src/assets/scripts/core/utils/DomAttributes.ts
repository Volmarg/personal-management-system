import DomElements  from "./DomElements";
import ArrayUtils   from "./ArrayUtils";

export default class DomAttributes {

    static readonly ATTRIBUTE_DISABLED = "disabled";

    public static actions = {
        set     : "set",
        unset   : "unset"
    };

    public static dataAttributes =  {
        contentEditable: "data-content-editable"
    };

    /**
     * @description This function will check if the element has attribute "content editable"
     *              If selector as second param is provided then found children will be checked
     *              If children is not found then throws error
     *
     * @param element
     * @param selectorToSearchInElement
     * @returns {boolean}
     */
    public static isContentEditable(element, selectorToSearchInElement: string = null): boolean
    {

        let isContentEditable = true;
        let childElement      = null;

        if( null !== selectorToSearchInElement ){
            childElement = DomElements.findChild(element, selectorToSearchInElement);
            element      = childElement;
        }

        if(
                typeof $(element).attr("contentEditable") == 'undefined'
            ||  $(element).attr("contentEditable")        != "true"
        ){
            isContentEditable = false;
        }

        return isContentEditable;
    };

    /**
     *  @description    This function will set or unset content editable attribute based on the "action" variable
     *                  If "excludeSelectorsForChildren"    is present then only the found children which does NOT contain children will be modified
     *                  If "selectorToSearchInElement"      is present then children will be searched and they will be modified
     *                  If "selectorsToSearchInChildren"    is present then it will search for children inside children
     *                      and they will be changed alongside with it's parents but not main parent
     *                  If "onlyForChildrenInChildren"      is present then only the children of v will be modified
     *                  Also by default this method will skip elements which have " data-content-editable="false" "
     *
     * @param element (jq elem)
     * @param action (set/unset)
     * @param selectorToSearchInElement string
     * @param excludedSelectorsForChildren string
     * @param selectorsToSearchInChildren string
     * @param onlyForChildrenInChildren bool
     */
    public static contentEditable(
        element,
        action                       : string,
        selectorToSearchInElement    : string  = null,
        excludedSelectorsForChildren : string  = null,
        selectorsToSearchInChildren  : string  = null,
        onlyForChildrenInChildren    : boolean = false
    ){

        let contentEditableState = null;

        switch( action ){
            case DomAttributes.actions.set:
                contentEditableState = "true";
                break;
            case DomAttributes.actions.unset:
                contentEditableState = "false";
                break;
            default:
                throw({
                    "message": "This action for toggling content editable is not defined",
                    "action" : action
                })
        }

        let firstLevelChildren  = null;
        let secondLevelChildren = null;

        if( null !== selectorToSearchInElement ){
            firstLevelChildren = DomElements.findChild(element, selectorToSearchInElement);
        }

        if( null !== excludedSelectorsForChildren){
            let filteredFirstLevelChildren = [];

            $.each(firstLevelChildren, (index, element) => {
                let foundExcludedElements = $(element).find(excludedSelectorsForChildren);

                if( 0 === foundExcludedElements.length ){
                    filteredFirstLevelChildren.push(element);
                }
            });

            firstLevelChildren = filteredFirstLevelChildren;
        }

        if( null !== selectorsToSearchInChildren ){
            secondLevelChildren = DomElements.findChild(firstLevelChildren, selectorsToSearchInChildren);
        }

        if( null === firstLevelChildren ){

            if( $(element).attr(DomAttributes.dataAttributes.contentEditable) !== "false" ){
                $(element).attr({"contentEditable": contentEditableState});
            }
        }

        if(
            null !== firstLevelChildren
            &&  null  == secondLevelChildren
        ){
            $.each(firstLevelChildren, (index, element) => {
                if( $(element).attr(DomAttributes.dataAttributes.contentEditable) !== "false" ){
                    $(element).attr({"contentEditable": contentEditableState});
                }
            })
        }

        if(
            null !== firstLevelChildren
            &&  null !== secondLevelChildren
            &&  !onlyForChildrenInChildren
        ){

            $.each(firstLevelChildren, (index, element) => {
                if( $(element).attr(DomAttributes.dataAttributes.contentEditable) !== "false" ){
                    $(element).attr({"contentEditable": contentEditableState});
                }
            });

            $.each(secondLevelChildren, (index, element) => {
                if( $(element).attr(DomAttributes.dataAttributes.contentEditable) !== "false" ){
                    $(element).attr({"contentEditable": contentEditableState});
                }
            });

        }else{

            $.each(secondLevelChildren, (index, element) => {
                if( $(element).attr(DomAttributes.dataAttributes.contentEditable) !== "false" ){
                    $(element).attr({"contentEditable": contentEditableState});
                }
            });
        }
    };

    /**
     * @description Will check if provided element is a checkbox, and if not then exception will be thrown,
     *              or exception will be skipped if second param will be passed
     *
     * @param element {object}
     * @param throwException {boolean}
     */
    public static isCheckbox(element, throwException: boolean = true): boolean
    {
        let type = $(element).attr('type');

        if( type !== "checkbox" ){

            if(throwException){
                throw({
                    "message": "This element is not a checkbox",
                    "element": element
                });
            }
            return false;
        }

        return true;
    };

    /**
     * @description Will mark checkbox as checked
     *
     * @param element {object}
     */
    public static setChecked(element): void
    {
        if( DomAttributes.isCheckbox(element) ){
            $(element).attr("checked", "checked");
            $(element).prop("checked", true);
        }
    };

    /**
     * @description Will uncheck checkbox
     *
     * @param element {object}
     */
    public static unsetChecked(element): void
    {
        if( DomAttributes.isCheckbox(element) ){
            $(element).removeAttr("checked");
            $(element).prop("checked", false);
        }
    };

    /**
     * @description Will check if checkbox is checked
     *
     * @param element {object}
     */
    public static isChecked(element): boolean
    {
        if( DomAttributes.isCheckbox(element) ){
            return $(element).prop("checked");
        }
    };

    /**
     * @description Will check if `disabled` class is present on element
     *
     * @param element {object}
     * @return {boolean}
     */
    public static isDisabledClass(element): boolean
    {
        let isDisabled = $(element).hasClass('disabled');
        return isDisabled;
    };

    /**
     * @description Will set disabled class
     *
     * @param element {object}
     */
    public static setDisabledClass(element): void
    {
        $(element).addClass("disabled");
    };

    /**
     * @description Will unset disabled class
     *
     * @param element {object}
     */
    public static unsetDisabledClass(element): void
    {
        $(element).removeClass("disabled");
    };

    /**
     * @description Will check if element has present/set `disabled property
     *
     * @param element {object}
     * @return {boolean}
     */
    public static isDisabledAttribute(element: JQuery<HTMLElement>): boolean
    {
        let isDisabled = (true == $(element).prop(DomAttributes.ATTRIBUTE_DISABLED));
        return isDisabled;
    };

    /**
     * @description Will set `disabled` property
     *
     * @param element {object}
     */
    public static setDisabledAttribute(element: JQuery<HTMLElement>): void
    {
        $(element).attr(DomAttributes.ATTRIBUTE_DISABLED, DomAttributes.ATTRIBUTE_DISABLED);
    };

    /**
     * @description Will unset `disabled` property
     *
     * @param element {object}
     */
    public static unsetDisabledAttribute(element: JQuery<HTMLElement>): void
    {
        $(element).removeAttr(DomAttributes.ATTRIBUTE_DISABLED);
    };

    /**
     * @description Add d-none class to element
     *
     * @param $element
     */
    public static setDisplayNoneClass($element): void
    {
        $element.addClass("d-none");
    }

    /**
     * Remove d-none class from element
     *
     * @param $element
     */
    public static unsetDisplayNoneClass($element): void
    {
        $element.removeClass("d-none");
    }

    /**
     * @description Check if element has d-none class
     *
     * @param $element
     */
    public static hasDisplayNoneClass($element): boolean
    {
        return $element.hasClass("d-none");
    }

    /**
     * @description Will set error class - mostly used for form elements
     *
     * @param element
     */
    public static setErrorClass(element): void
    {
        $(element).addClass("has-error");
    };

    /**
     * @description Will unset error class
     *
     * @param element
     */
    public static unsetErrorClass(element): void
    {
        $(element).removeClass("has-error");
    }

    /**
     * @description this function will trim the selector elements like `#` , `.` to get the className
     *              the classname is used by jquery `hasClass` etc.
     *              Keep in mind that this won't work with multiple classes and attribute selectors
     */
    public static getClassNameFromSelector(selector: string): string
    {
        return selector.replace(/[#.]?/g,"");
    }

    /**
     * @description Will check if target DOM element has given attribute
     *              Does not rely on the value but just if the attribute is there at all
     *
     * @param $element
     * @param attributeName
     */
    public static hasAttribute($element: JQuery<HTMLElement>, attributeName: string): boolean
    {
        return !( "undefined" === typeof $element.attr(attributeName) );
    }

}