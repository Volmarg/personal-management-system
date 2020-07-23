// import Ajax from "../../core/ui/Ajax"; - todo:

export default class DomElements {

    /**
     * AjaxGuiElementsReload ajaxGuiElementsReload
     */
    // private ajaxGuiElementsReload = new Ajax();

    /**
     * Checks if there are existing elements for domElements selected with $();
     * @param elements
     * @returns {boolean}
     */
    public static doElementsExists(elements): boolean
    {
        return 0 !== $(elements).length;
    }

    /**
     * This function is a find() decorator but it will throw exception if element was not found
     * This is needed as some functionality MUST be executed so missing child element is a bug
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
     * Fetches the form view for given form name and appends it to the targetSelector
     * @param formName
     * @param targetSelector
     * @param callbackParam {function}
     */
    public appendFormView(formName: string, targetSelector: string, callbackParam: Function): void
    {

        let $targetElement = $(targetSelector);

        if( 0 === $targetElement.length ){
            throw ({
                "message"   : "No element with given selector was found",
                "selector"  : targetSelector
            })
        }

        try{
            var namespace = dataProcessors.forms[formName].getFormNamespace();
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
        };

        // this.ajaxGuiElementsReload.getFormViewByNamespace(namespace, callback);
    };

    /**
     * This function will remove the closest element which matches the selector relative to $element
     */
    public removeClosesSelecotrParentForElement($element: JQuery, selector: string): void
    {
        let parentToRemove = $($element).closest(selector);
        parentToRemove.remove();
    }
}