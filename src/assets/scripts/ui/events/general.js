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
                appendForm: '.append-form'
            }
        },
        init: function(){
            this.attachFormViewAppendEvent();
        },
        attachFormViewAppendEvent: function(){

            let targetElements = $(this.selectors.classes.appendForm);

            if( !utils.validations.doElementsExists(targetElements) ){
                return;
            }

            $(targetElements.on('click', function(){
                let targetElementSelector = $(this).attr('data-target-selector');
                let formName              = $(this).attr('data-form-name');

                utils.domElements.appendFormView(formName, targetElementSelector);
            }))

        }
    };

}());


