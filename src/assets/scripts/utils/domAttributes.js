export default (function () {

    if (typeof utils === 'undefined') {
        window.utils = {}
    }

    utils.domAttributes = {

        actions:{
            set     : "set",
            unset   : "unset"
        },
        dataAttributes: {
          contentEditable: "data-content-editable"
        },
        /**
         * This function will check if the element has attribute "content editable"
         * If selector as second param is provided then found children will be checked
         * If children is not found then throws error
         * @param element
         * @param selectorToSearchInElement
         * @returns {boolean}
         */
        isContentEditable: function (element, selectorToSearchInElement = null) {

            let isContentEditable = true;
            let childElement      = null;

            if( null !== selectorToSearchInElement ){
                childElement = utils.domElements.findChild(element, selectorToSearchInElement);
                element      = childElement;
            }

            if(
                    typeof $(element).attr("contentEditable") == 'undefined'
                ||  $(element).attr("contentEditable")        != "true"
            ){
                isContentEditable = false;
            }

            return isContentEditable;
        },
        /**
         *  This function will set or unset content editable attribute based on the "action" variable
         *  If "excludeSelectorsForChildren"    is present then only the found children which does NOT contain children will be modified
         *  If "selectorToSearchInElement"      is present then children will be searched and they will be modified
         *  If "selectorsToSearchInChildren"    is present then it will search for children inside children and they will be changed alongside with it's parents but not main parent
         *  If "onlyForChildrenInChildren"      is present then only the children of v will be modified
         *  Also by default this method will skip elements which have " data-content-editable="false" "
         * @param element (jq elem)
         * @param action (set/unset)
         * @param selectorToSearchInElement string
         * @param excludedSelectorsForChildren string
         * @param selectorsToSearchInChildren string
         * @param onlyForChildrenInChildren bool
         */
        contentEditable: function(element, action, selectorToSearchInElement = null, excludedSelectorsForChildren = null, selectorsToSearchInChildren = null, onlyForChildrenInChildren = false){

            let contentEditableState = null;

            switch( action ){
                case this.actions.set:
                    contentEditableState = "true";
                    break;
                case this.actions.unset:
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
                firstLevelChildren = utils.domElements.findChild(element, selectorToSearchInElement);
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
                secondLevelChildren = utils.domElements.findChild(firstLevelChildren, selectorsToSearchInChildren);
            }

            if( null === firstLevelChildren ){

                if( $(element).attr(this.dataAttributes.contentEditable) !== "false" ){
                    $(element).attr({"contentEditable": contentEditableState});
                }
            }

            if(
                    null !== firstLevelChildren
                &&  null  == secondLevelChildren
            ){
                $.each(firstLevelChildren, (index, element) => {
                    if( $(element).attr(this.dataAttributes.contentEditable) !== "false" ){
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
                    if( $(element).attr(this.dataAttributes.contentEditable) !== "false" ){
                        $(element).attr({"contentEditable": contentEditableState});
                    }
                });

                $.each(secondLevelChildren, (index, element) => {
                    if( $(element).attr(this.dataAttributes.contentEditable) !== "false" ){
                        $(element).attr({"contentEditable": contentEditableState});
                    }
                });

            }else{

                $.each(secondLevelChildren, (index, element) => {
                    if( $(element).attr(this.dataAttributes.contentEditable) !== "false" ){
                        $(element).attr({"contentEditable": contentEditableState});
                    }
                });

            }

        },
        /**
         * Will check if provided element is a checkbox, and if not then exception will be thrown,
         * or exception will be skipped if second param will be passed
         * @param element {object}
         * @param throwException {boolean}
         */
        isCheckbox(element, throwException = true){
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
        },
        /**
         * Will mark checkbox as checked
         * @param element {object}
         */
        setChecked: function(element){
            if( this.isCheckbox(element) ){
                $(element).attr("checked", "checked");
            }
        },
        /**
         * Will uncheck checkbox
         * @param element {object}
         */
        unsetChecked: function(element){
            if( this.isCheckbox(element) ){
                $(element).removeAttr("checked");
            }
        },
        /**
         * Will check if checkbox is checked
         * @param element {object}
         */
        isChecked: function(element){
            if( this.isCheckbox(element) ){
                let checkedAttr     = $(element).attr("checked");
                let checkedValues   = [
                    "true", true, "checked"
                ];

                let isInArray = utils.array.inArray(checkedAttr, checkedValues);

                return isInArray;
            }
        },
        /**
         * Will check if checkbox is checked
         * @param element {object}
         * @return {boolean}
         */
        isDisabled: function(element){
            let isDisabled = $(element).hasClass('disabled');
            return isDisabled;
        },
        /**
         * Will set disabled class
         * @param element {object}
         */
        setDisabled: function(element){
            $(element).addClass("disabled");
        },
        /**
         * Will unset disabled class
         * @param element {object}
         */
        unsetDisabled: function(element){
            $(element).removeClass("disabled");
        },
        /**
         * Will set error class - mostly used for form elements
         * @param element
         */
        setErrorClass: function(element){
            $(element).addClass("has-error");
        },
        /**
         * Will unset error class
         * @param element
         */
        unsetErrorClass: function(element){
            $(element).removeClass("has-error");
        }

    };


}());