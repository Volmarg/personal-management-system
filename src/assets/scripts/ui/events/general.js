/**
 * todo: move later events from crud to events/crud.js
 * This file contains events that are attached on different sections of pages
 */

/**
 * @info: This class contains the methods and representations used to:
 *  - handle formView fetching from backed
 */

export default (function () {

    if (typeof events === 'undefined') {
        window.events = {}
    }

    events.general = {
        selectors: {
            classes: {
                appendForm  : '.append-form',
                removeParent: '.remove-parent'
            }
        },
        init: function(){
            this.attachFormViewAppendEvent();
            this.attachRemoveParentEvent();
        },
        /**
         * This function attaches event on button which clicked appends given form to dom target
         */
        attachFormViewAppendEvent: function(){

            let targetElements = $(this.selectors.classes.appendForm);
            let _this          = this;

            if( !utils.validations.doElementsExists(targetElements) ){
                return;
            }

            let callback = function(){
                _this.attachRemoveParentEvent();
            };

            $(targetElements.on('click', function(){
                let targetElementSelector = $(this).attr('data-target-selector');
                let formName              = $(this).attr('data-form-name');

                utils.domElements.appendFormView(formName, targetElementSelector, callback);
            }))

        },
        /**
         * This function attaches event which removes the parent element with given selector
         */
        attachRemoveParentEvent: function(){
            let targetElements = $(this.selectors.classes.removeParent);

            if( !utils.validations.doElementsExists(targetElements) ){
                return;
            }

            $(targetElements.on('click', function(){
                let targetElementSelector = $(this).attr('data-removed-selector');
                let clickedElement        = $(this);

                utils.domElements.removeParentForSelector(clickedElement, targetElementSelector);
            }))

        }
    };

}());


