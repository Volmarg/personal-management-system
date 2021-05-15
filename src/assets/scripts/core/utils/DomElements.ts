import BackendStructureLoader   from "../ui/BackendStructure/BackendStructureLoader";
import Ajax                     from "../ajax/Ajax";
import ArrayUtils               from "./ArrayUtils";
import StringUtils from "./StringUtils";

export default class DomElements {

    /**
     *@type Object
     */
    private dataAttributes = {
        hideDomElement                         : 'data-hide-dom-element',
        hideDomElementTargetSelector           : 'data-hide-dom-element-target-selector',
        hideDomElementForOptionsValues         : 'data-hide-dom-element-for-options-values',
        hideDomElementForOptionsParentSelector : 'data-hide-dom-element-target-parent-selector'
    };

    /**
     * @description will initialize logic for dom elements
     */
    public init(): void
    {
        this.showDomElementForSelectedOptionInSelect();
    }

    /**
     * @description Checks if there are existing elements for domElements selected with $();
     *
     * @param elements
     * @returns {boolean}
     */
    public static doElementsExists(elements: JQuery<HTMLElement> | JQuery<HTMLElement>[] | JQuery<JQuery<HTMLElement>[]>): boolean
    {
        return 0 !== $(elements).length;
    }

    /**
     * @description This function is a find() decorator but it will throw exception if element was not found
     *              This is needed as some functionality MUST be executed so missing child element is a bug
     *
     * @param element
     * @param selector
     * @returns {boolean}
     */
    public static findChild(element: JQuery, selector: string): JQuery
    {
        let childElement = $(element).find(selector);

        if( 0 === $(childElement).length)
        {
            throw({
                "message"        : "Could not find the selector for element.",
                "element"        : element,
                "selectorToFind" : selector
            })
        }

        return childElement;
    };

    /**
     * @description Fetches the form view for given form name and appends it to the targetSelector
     *
     * @param formName
     * @param targetSelector
     * @param callbackParam {function}
     */
    public static appendFormView(formName: string, targetSelector: string, callbackParam: Function): void
    {
        let ajax           = new Ajax();
        let $targetElement = $(targetSelector);

        if( 0 === $targetElement.length ){
            throw ({
                "message"   : "No element with given selector was found",
                "selector"  : targetSelector
            })
        }

        try{
            var backendStructure = BackendStructureLoader.getNamespace(BackendStructureLoader.STRUCTURE_TYPE_FORM, formName);
            var namespace        = backendStructure.getNamespace();
        }catch(Exception){
            throw({
                'message'   : "Could not load form namespace from data processors.",
                'formName'  : formName
            })
        }

        let callback = function(formView){
            $targetElement.append(formView);
            if( "function" === typeof callbackParam){
                callbackParam();
            }
            backendStructure.getCallback()();
        };

        ajax.getFormViewByNamespace(namespace, callback);
    };

    /**
     * @description This function will remove the closest element which matches the selector relative to $element
     * 
     */
    public static removeClosestSelectorParentForElement($element: JQuery, selector: string): void
    {
        let parentToRemove = $($element).closest(selector);
        parentToRemove.remove();
    }

    /**
     * @description this function will show given DOM element (by target selector) only when the target select
     *              has explicit options selected
     */
    private showDomElementForSelectedOptionInSelect()
    {
        let $allSelectorsWithHidingLogic = $("[" + this.dataAttributes.hideDomElement + "]");
        let _this                        = this;

        $.each($allSelectorsWithHidingLogic, (index, element) => {
            let $element = $(element);

            $element.on('change', function(event){
                let $changedSelectElement = $(event.currentTarget);
                let selectedOptionValue   = $changedSelectElement.val() as string;

                try {
                    var targetDomElementSelectorToHide       = $changedSelectElement.attr(_this.dataAttributes.hideDomElementTargetSelector);
                    var targetDomElementParentSelectorToHide = $changedSelectElement.attr(_this.dataAttributes.hideDomElementForOptionsParentSelector);
                    var $targetDomElementToHide              = $(targetDomElementSelectorToHide);

                    var optionsValuesToHideDomElementFor = JSON.parse($changedSelectElement.attr(_this.dataAttributes.hideDomElementForOptionsValues));
                }catch(Exception){
                    throw {
                        "message"   : "Could not read/parse data attributes for showing dom elements depending on selected option",
                        "exception" : Exception
                    }
                }

                if( 0 === optionsValuesToHideDomElementFor.length ){
                    throw{
                        "message": "No options were provided for which dom element should be shown"
                    }
                }

                // if parent selector is defined then closest parent of target element will be searched and that element will be toggled
                if( !StringUtils.isEmptyString(targetDomElementParentSelectorToHide) ){
                    $targetDomElementToHide = $targetDomElementToHide.closest(targetDomElementParentSelectorToHide)
                }

                if( !DomElements.doElementsExists($targetDomElementToHide) ){
                    throw{
                        "message"  : "No dom element was found for given selector",
                        "selector" : targetDomElementSelectorToHide
                    }
                }

                if( ArrayUtils.inArray(selectedOptionValue, optionsValuesToHideDomElementFor) ){
                    $targetDomElementToHide.show();
                }else{
                    $targetDomElementToHide.hide();
                }

            })
        })

        // this trigger is required to initially hide/show DOM Elements
        $allSelectorsWithHidingLogic.trigger('change');
    }

}