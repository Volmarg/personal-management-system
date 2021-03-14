import AbstractAction   from "./AbstractAction";
import DomElements      from "../../utils/DomElements";
import FormsUtils       from "../../utils/FormsUtils";

export default class FormAppendAction extends AbstractAction {

    /**
     * @type Object
     */
    private selectors = {
        classes: {
            appendForm  : '.append-form',
            removeParent: '.remove-parent'
        }
    };

    /**
     * @type FormsUtils
     */
    private formsUtils = new FormsUtils();

    /**
     * Main initialization logic
     */
    public init(){
        this.attachFormViewAppendEvent();
        this.attachRemoveParentEvent();
        this.attachFormElementsOnChangeValidationEvent();
    };

    /**
     * This function attaches event on button which clicked appends given form to dom target
     */
    public attachFormViewAppendEvent() {

        let targetElements = $(this.selectors.classes.appendForm);
        let _this          = this;

        if( !DomElements.doElementsExists(targetElements) ){
            return;
        }

        let callback = function(){
            _this.attachRemoveParentEvent();
        };

        $(targetElements.on('click', function(event){
            event.preventDefault();
            let targetElementSelector = $(this).attr('data-target-selector');
            let formName              = $(this).attr('data-form-name');

            // public non static
            DomElements.appendFormView(formName, targetElementSelector, callback);
        }))

    };

    /**
     * This function attaches event which removes the parent element with given selector
     */
    public attachRemoveParentEvent() {
        let targetElements = $(this.selectors.classes.removeParent);

        if( !DomElements.doElementsExists(targetElements) ){
            return;
        }

        $(targetElements.on('click', function(event){
            event.preventDefault();
            let targetElementSelector = $(this).attr('data-removed-selector');
            let clickedElement        = $(this);

            DomElements.removeClosestSelectorParentForElement(clickedElement, targetElementSelector);
        }))

    };

    /**
     * This function will attach events for validations and limitations to the various form elements
     */
    private attachFormElementsOnChangeValidationEvent() {
        let _this              = this;
        let elementsToValidate = $("[data-validate-form-element='true']");

        elementsToValidate.on('change', function(event){
            let changedElement = $(event.currentTarget);

            _this.formsUtils.validateBetweenMinMax(changedElement);

        });

    };
}