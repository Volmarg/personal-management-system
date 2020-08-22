import * as $ from 'jquery';
import BootstrapNotify from "../../libs/bootstrap-notify/BootstrapNotify";

/**
 * @description Handles marking incorrect form fields and displaying validation fail messages
 */
export default class FormsValidator {

    private _selectors = {
        data: {
            validableForm: "data-validable-form",
        }
    };

    private _classes = {
        formElementInvalid : "is-invalid",
        formElementValid   : "is-valid",
    };

    /**
     * @type {string}
     */
    private formPrefix = "";

    /**
     * @type {array}
     */
    private invalidFields = [];

    constructor(formPrefix:string, invalidFields:string[])
    {
        this.formPrefix    = formPrefix;
        this.invalidFields = invalidFields;
    }

    get selectors(): {} {
        return this._selectors;
    }

    /**
     * @description Will build element id, search for it and then display errors
     */
    public handleInvalidFields(): void
    {
        let _this = this;
        this.clearFormErrors();

        // @ts-ignore
        $.each(_this.invalidFields, (fieldName:string, message) => {
            let elementId    = _this.buildSymfonyGeneratedFormElementId(fieldName);
            let $formElement = $("#" + elementId);

            _this.markFormElementRed($formElement);
            _this.showValidationErrorMessage(message)
        })

    }

    /**
     * @description will build the id selector for form element just like backend symfony does it,
     *              this will be used to find given form element with JQ
     */
    private buildSymfonyGeneratedFormElementId(fieldName:string):string
    {
        let id = this.formPrefix + "_" + fieldName;
        return id;
    }

    /**
     * @description Will show error popups - this ones needs to be closed manually
     *
     * @param message {string}
     */
    private showValidationErrorMessage(message:string): void
    {
        let bootstrapNotify = new BootstrapNotify();
        bootstrapNotify.showOrangeNotification(message, 5000, false);
    }

    /**
     * @description Will mark the field
     */
    private markFormElementRed($formElement:JQuery)
    {
        $formElement.addClass(this._classes.formElementInvalid);
    }

    /**
     * @description Will remove red error markings
     */
    private clearFormErrors(): void
    {
        let _this         = this;
        let $form         = $("form[name='" + this.formPrefix +"']");
        let $formElements = $form.find('input, textarea');

        $.each($formElements, (index, element) => {
            let $element = $(element);
            $element.removeClass(_this._classes.formElementInvalid);
        })
    }

}