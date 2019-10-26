export default (function () {

    if (typeof utils === 'undefined') {
        window.utils = {}
    }

    utils.domElements = {
        /**
         * This function is a find() decorator but it will throw exception if element was not found
         * This is needed as some functionality MUST be executed so missing child element is a bug
         * @param element
         * @param selector
         * @returns {boolean}
         */
        findChild: function (element, selector) {

            let childElement = $(element).find(selector);

            if( 0 === $(childElement).length)
            {
                throw({
                    "message"        : "Could not find the selector for element.",
                    "element"        : element,
                    "selectorToFind" : selectorToSearchInElement
                })
            }

            return childElement;
        },
        setContentEditable: function(element, selectorToSearchInElement = null){
            let childElement = null;

            if( null !== selectorToSearchInElement ){
                childElement = $(element).find(selectorToSearchInElement);

                if( 0 === $(childElement).length)
                {
                    throw({
                        "message"        : "Could not find the selector for element.",
                        "element"        : element,
                        "selectorToFind" : selectorToSearchInElement
                    })
                }
                element = childElement;
            }

        },
        unsetContentEditable: function(element, selectorToSearchInElement = null){

        },
    };


}());