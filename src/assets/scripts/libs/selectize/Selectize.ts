import 'selectize/dist/js/selectize.min.js';
import 'selectize/dist/css/selectize.css';
import 'selectize/dist/css/selectize.bootstrap3.css';

import * as $       from 'jquery';
import StringUtils  from "../../core/utils/StringUtils";
import DomElements  from "../../core/utils/DomElements";

export default class Selectize {

    private static attributes = {
        dataInlineCss: 'data-inline-css', // if present - will add inline rule to the selectize input (is block by default)
    }

    /**
     * @description Main initialization logic
     */
    public init(): void
    {
        this.applyTagsSelectize();
        this.disableTagsInputsForSelectorsOnPage();
    }

    /**
     * @description Will apply selectize to given inputs
     */
    public applyTagsSelectize(): void
    {
        let allTagsInputsFields = $('input.tags');
        let _this               = this;

        // init tags with data from server
        $.each(allTagsInputsFields, (index, input) => {
            _this.applyTagsSelectizeForSingleInput(input);
        });
    };

    /**
     * @description will apply tags selectize to single input element
     */
    public applyTagsSelectizeForSingleInput(input: HTMLElement)
    {
        let jsonValues   = $(input).attr('data-value');
        let objectValues = [];
        if( !StringUtils.isEmptyString(jsonValues) ){
            objectValues = JSON.parse(jsonValues);
        }
        // @ts-ignore
        let selectize = $(input).selectize({
            persist     : false,
            createOnBlur: true,
            create      : true,
        });

        this.addTagsToSelectize(selectize, objectValues);
        this.applyStylingAndManipulationForSelectizedInput(input);
    }

    /**
     * Adds array of tags to selectize instance
     *
     * @param selectize     {object}
     * @param arrayOfTags   {object}
     */
    private addTagsToSelectize(selectize: object, arrayOfTags: object): void
    {
        var selectize_element = selectize[0].selectize;

        $.each(arrayOfTags, (index, value) => {
            selectize_element.addOption({
                text    : value,
                value   : value
            });
            selectize_element.refreshOptions() ;
            selectize_element.addItem(value);
        });
    };

    /**
     * @description will handle any kind of logic related to styling the selectized input element
     *
     * @param originalDomElement {HTMLElement}
     */
    private applyStylingAndManipulationForSelectizedInput(originalDomElement: HTMLElement): void
    {
        let $originalElement = $(originalDomElement);
        let isInlineCss      = (0 !== $originalElement.filter("[" + Selectize.attributes.dataInlineCss + "]").length);

        if(isInlineCss){
            let $selectizeWrapper    = $originalElement.parent().find('.selectize-control');
            let $selectizeSubWrapper = $originalElement.parent().find('.selectize-input');

            $selectizeWrapper.addClass('d-inline-block');
            $selectizeSubWrapper.css({
               "overflow": "visible",
            });
        }
    }

    /**
     * Will set given selectize inputs to disable by adding class
     */
    public disableTagsInputsForSelectorsOnPage(): void
    {

        let disableForSelectorsOnPage = ['#MyFiles .selectize-control'];

        // search for selectors on page and if found disable tags
        $.each(disableForSelectorsOnPage, (index, selector) => {
            if ( DomElements.doElementsExists($(selector)) )
            {
                let allSelectizeRenderedInputWrappers = $(selector);
                $(allSelectizeRenderedInputWrappers).addClass('disabled');

                return false;
            }
        });

    }
}