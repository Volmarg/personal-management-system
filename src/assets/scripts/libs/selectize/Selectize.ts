import 'selectize/dist/js/selectize.min.js';
import 'selectize/dist/css/selectize.css';
import 'selectize/dist/css/selectize.bootstrap3.css';

import * as $       from 'jquery';
import StringUtils  from "../../core/utils/StringUtils";
import DomElements  from "../../core/utils/DomElements";

export default class Selectize {

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

            _this.addTagsToSelectize(selectize, objectValues);

        });
    };

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
     * Will set given selectize inputs to disable by addig class
     */
    public disableTagsInputsForSelectorsOnPage(): void
    {

        let disableForSelectorsOnPage = ['#MyFiles .selectize-control'];

        // search for selectors on page and if found disable tags
        $.each(disableForSelectorsOnPage, (index, selector) => {
            if ( DomElements.doElementsExists($(selector)) )
            {
                let allSelectizeRenderdInputWrappers = $(selector);
                $(allSelectizeRenderdInputWrappers).addClass('disabled');

                return false;
            }
        });

    }
}